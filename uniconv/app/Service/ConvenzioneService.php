<?php

namespace App\Service;

use Carbon\Carbon;
use App\Convenzione;
use App\UserTask;
use App\Permission;
use App\Scadenza;
use App\Personale;
use App\Repositories\ConvenzioneRepository;
use DateTime;
use Auth;
use App;
use App\Http\Resources\WorkflowConvenzione;
use App\Http\Resources\WorkflowConvenzioneSchemaTipoResource;
use Workflow;
use App\User;
use App\Notifications\ScadenzaEmessa;
use App\Notifications\ConvenzioneApprovata;
use App\Notifications\ConvenzioneRepertoriata;
use App\Notifications\RichiestaEmissione;
use App\Notifications\RichiestaValidazione;

use App\Tasks\SottoscrizioneTask;
use App\Tasks\GenericTask;
use App\Tasks\EmissioneTask;
use App\Tasks\InPagamentoTask;
use App\Tasks\RichiestaValidazioneTask;

use App\AttachmentType;
use Exception;

use App\Http\Controllers\SoapControllerTitulus;
use Artisaninweb\SoapWrapper\SoapWrapper;
use App\Soap\Request\SaveDocument;
use App\Soap\Request\SaveParams;
use App\Soap\Request\AttachmentBean;
use App\Models\Titulus\Fascicolo;
use App\Models\Titulus\Documento;
use App\Models\Titulus\Rif;
use App\Models\Titulus\Element;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Dipartimento;
class ConvenzioneService implements ApplicationService
{

   /**
     * @var ConvenzioneRepository
     */
    private $convenzioneRepository;

    /**
     * BusinessService constructor.
     * @param ConvenzioneRepository $convenzioneRepository
     */
    public function __construct(ConvenzioneRepository $convenzioneRepository)
    {
        $this->convenzioneRepository = $convenzioneRepository;      
    }

    public function nextPossibleActions($id){
        $conv = Convenzione::withTrashed()->find($id);        
        WorkflowConvenzione::withoutWrapping();
        return WorkflowConvenzione::collection(collect($conv->workflow_transitions_self()));                     
    }

    public function createConvenzione($request){

        if ($request->convenzione_from == 'dip'){
            $request->validate([
                'resp_scientifico'=>'required',
            ]);
            return $this->create($request->all());
        }else if ($request->convenzione_from == 'amm'){

            //se utente è superadmin oppure afferenza organizzativa
            //compatibile con unita organizzativa della convenzione
            $request->validate([
                'unitaorganizzativa_uo'=>'required',
            ]);
            return $this->create_amministrativa($request->all());
        }

    }

    public function createFascicolo($data){
        $sc = new SoapControllerTitulus(new SoapWrapper);        
        $fasc = new Fascicolo;
        $fasc->oggetto = $data['oggetto_fascicolo'];                
        $fasc->addClassifCod($data['titolario_classificazione']);

        //se c'è il dipartimento...
        if ($data['dipartimemto_cd_dip']){
            //...prendiamo il codice dell'unità organizzativa che fa riferimento al dipartimento
            $dip = Dipartimento::find($data['dipartimemto_cd_dip']);
            $dip_unitaorganizzativa = $dip->unitaOrganizzativa()->first();
            $dip_unitaorganizzativa_uo = $dip_unitaorganizzativa->uo;
            TitulusHelper::addRPA_Titulus($fasc, $dip_unitaorganizzativa_uo);
        }else if ($data['unitaorganizzativa_uo']){
            //caso convenzione amministrativa
            $aff_org = $data['unitaorganizzativa_uo'];                        
            TitulusHelper::addRPA_Titulus($fasc,$aff_org);        
        }else{
            //...altrimenti l'unità oranizzativa della persona che esegue la convenzione
            $aff_org = Auth::user()->personale->aff_org;            
            TitulusHelper::addRPA_Titulus($fasc,$aff_org);        
        }

        if (App::environment(['local','preprod'])) {         
            $fasc->addCC("Attività sistemistiche e software Gestionali e Documentali", "Oliva Enrico");
        }

        $response = $sc->newFascicolo($fasc->toXml());                

        $obj = simplexml_load_string($response);
        
        $data['nrecord'] = (string)$obj->Document->fascicolo['nrecord'];
        $data['numero'] = (string)$obj->Document->fascicolo['numero'];

        return $data;
    }
  
    /**
     * create_amministrativa
     *
     * Una convenzione amministrativa ha 
     * l'attributo convenzione_from = amm
     * e schematipotipo = 'daapprovare'
     * e dipartimento a null
     * e dipartimento_id a null
     * viene aperta una richiesta di validazione sull'utente che sta inserendo la convenzione
     * 
     * @param  mixed $data
     * @return Convenzione
     */
    public function create_amministrativa($data){

        //unità organizzativa a cui è associata la convenzione è quella dell'utente che sta inserendo la convenzione
        //se è un utente che ha responsabilità su più uffici amministrativi, viene selezionato il principale
        $user = User::find($data['user']['id']);                             
        $data = $this->createFascicolo($data);        

        //crea nuovo         
        $data['dipartimemto_cd_dip'] = null;
        $data['schematipotipo'] = 'daapprovare';        
        
        $conv = $this->convenzioneRepository->create($data);  
        
        $conv->workflow_apply('store_to_inapprovazione', $conv->getWorkflowName());    
        
        $conv->save();        
        
        //creare e salvare un usertask 
        $usertask = new RichiestaValidazioneTask($conv);
        $usertask->assignments(Auth::user()->id);
        $usertask->owner(Auth::user()->id)->description('Approvazione');
        $usertask->save();

        return $conv;
    }

    public function create($data){

        // unità organizzativa a cui è associata la convenzione quella dell'utente che sta inserendo la convenzione
        $user = User::find($data['user']['id'])->findPersonaleRespons();                            
        $data['unitaorganizzativa_uo'] = $user->cd_csa;
        
        //crea nuovo         
        $conv = $this->convenzioneRepository->create($data);   
        if ($conv->schematipotipo == 'schematipo'){
            //passare allo stato di in_sottoscrizione
            $conv->workflow_apply('store_to_approvato', $conv->getWorkflowName());
            $conv->save();

            $task = (new SottoscrizioneTask($conv))->owner($conv->user_id);
            $task->save();
                   
        }else{                                      
            $conv->workflow_apply('store_to_inapprovazione', $conv->getWorkflowName());        
            $conv->save();

            //creare e salvare un usertask 
            $usertask = new RichiestaValidazioneTask($conv);
            $usertask->owner(Auth::user()->id)
                ->setAssignments($data['assignments'])
                ->respons($data['respons_v_ie_ru_personale_id_ab'])
                ->unitaorganizzativa($data['unitaorganizzativa_affidatario'])
                ->data([                    
                    'description' => $data['description']                    
                ]);
            $usertask->save();
            
            //notifica di richiesta validazione
            foreach ($data['assignments'] as $key => $value ) {               
                try {                    
                    //TUTTI gli assegnatari devono essere registrati...
                    //se l'utente non c'è ... registralo
                    $user = User::where('v_ie_ru_personale_id_ab', $value['v_ie_ru_personale_id_ab'])->first();                
                    if ($user==null){      
                        $user = $this->addUser($value['v_ie_ru_personale_id_ab']);                              
                    }
                        
                    $user->notify(new RichiestaValidazione($conv, $data));    
                } catch (Exception $e) {                                                            
                    Log::error('Notifica richiesta validazione non inviata utente [' .$user->email. ']');       
                    Log::error($e);
                    //throw $e;
                }
            }

        }

        return $conv;
    }

    public function updateValidationStep($request){
        $conv = $this->convenzioneRepository->updateValidationStep($request->all(), Convenzione::STORE_VALIDAZIONE);                   

        //chiudere i task associati ... a questa convenzione in stato ... approvato
        $tasks = $conv->usertasks()->where('workflow_transition',Convenzione::STORE_VALIDAZIONE)->get();
        $this->closeTasks($tasks);

        $task = (new SottoscrizioneTask($conv))->owner($conv->user_id);
        $task->save();

        //notifica al possessore della convenzione dell'avvenuta approvazione
        try {
            //la notifica va fatta a chi ha aperto il task che coincide con quello che ha creato la convenzione
            $user = User::find($conv->user_id);
            $user->notify(new ConvenzioneApprovata($conv));    
        } catch (Exception $e) {
            Log::error('Notifica non inviata utente [' .$conv->user_id. ']');       
        }
      
        return $conv;
    }
    
    private function docInEntrata($objdata, &$data){
        //registrazione con protocollo 
        if ($objdata->attachment1['attachmenttype_codice'] == 'LTE_FIRM_CONTR_PROT'){        
            $attch['attachmenttype_codice'] = 'LTE_FIRM_CONTR_PROT';
            $doc = $objdata->attachment1['doc'];

            $attch['nrecord'] = $doc['nrecord'];
            $attch['num_prot'] = $doc['num_prot'];
            $attch['emission_date'] =  $doc['data_prot']; 

            $data['attachments'] = array($attch);                
        }

        //registrazione documento
        if ($objdata->attachment1['attachmenttype_codice'] == 'LTE_FIRM_CONTR'){
            $data['attachments'] = array($objdata->attachment1);                                                    
        }             
    }

    private function docInUscita($objdata, &$data){
        //registrazione con protocollo 
        if ($objdata->attachment1['attachmenttype_codice'] == 'LTU_FIRM_UNIURB_PROT'){        
            $attch['attachmenttype_codice'] = 'LTU_FIRM_UNIURB_PROT';
            $doc = $objdata->attachment1['doc'];

            $attch['nrecord'] = $doc['nrecord'];
            $attch['num_prot'] = $doc['num_prot'];
            $attch['emission_date'] =  $doc['data_prot']; 

            $data['attachments'] = array($attch);                
        }

        //registrazione documento
        if ($objdata->attachment1['attachmenttype_codice'] == 'LTU_FIRM_UNIURB'){
            $data['attachments'] = array($objdata->attachment1);                                                    
        }             
    }
    
    public function openTaskCompletamentoSottoscrizione($conv){
        $task = (new GenericTask($conv))
            ->owner($conv->user_id)            
            ->workflow_place($conv->current_place)
            ->workflow_transition($conv->workflow_transitions()[0]->getName());

        if ($conv->stipula_type == 'controparte') {        
            $task->subject('Firma da UniUrb')->description('Portare alla firma la convenzione già firmata dalla controparte');
        } else {
            $task->subject('Firma della controparte')->description('Richiedere la firma della contraparte sulla convenzione già firmata da UniUrb');
        }

        $task->save();
    }

    public function cancellazioneSottoscrizione($request){
        $data = $request->all();        

        $conv = $this->convenzioneRepository->deleteSottoscrizioneStep($data);   

        //cancellare tutti i task aperti di completamento
        //cancellare i task associati allo step di completamento della sottoscrizione
        $tasks = $conv->usertasks()->where('workflow_place',$data['current_place'])->delete();

        //eliminare i task che hanno chiuso la sottoscrizione 
        $tasks = $conv->usertasks()->where('workflow_place',Convenzione::APPROVATO)->delete();
        
        //creazione task sottoscrizione
        $task = (new SottoscrizioneTask($conv))->owner($conv->user_id);
        $task->save();

        $response = [
            "status" => "success", 
            "message"=> "Cancellazione sottoscrizione eseguita con successo. \n",                
            "data" => $conv
        ];

        return $response;


    }

    public function registrazioneSottoscrizione($request){

        $data = $request->all();
        $conv = Convenzione::findOrFail($data['convenzione_id']);            
        $objdata = null;

        //caso cartaceo uniub
        if ($request->stipula_type == Convenzione::STIPULA_UNIURB && $request->stipula_format == Convenzione::STIPULA_ANALOGICA){
            $objdata = (object) $data['cartaceo_uniurb'];            
            $this->docInUscita($objdata,$data);                    
        }

        //caso digitale uniurb
        if ($request->stipula_type == Convenzione::STIPULA_UNIURB && $request->stipula_format == Convenzione::STIPULA_DIGITALE){
            $objdata = (object) $data['digitale_uniurb'];   
            $this->docInUscita($objdata,$data);   
        }
          
        //caso cartacea controparte(ditta)
        if ($request->stipula_type == Convenzione::STIPULA_CONTROPARTE && $request->stipula_format == Convenzione::STIPULA_ANALOGICA){
            $objdata = (object) $data['cartaceo_controparte'];  
            $this->docInEntrata($objdata,$data);
        }

        //caso cartacea controparte(ditta)
        if ($request->stipula_type == Convenzione::STIPULA_CONTROPARTE && $request->stipula_format == Convenzione::STIPULA_DIGITALE){
            $objdata = (object) $data['digitale_controparte'];              
            $this->docInEntrata($objdata,$data);
        }

        //nessun documento
        if ($objdata->attachment1['attachmenttype_codice'] == 'NESSUN_DOC'){
            $data['data_sottoscrizione'] =  $objdata->attachment1['data_sottoscrizione'];                                       
        }

        //$data['attachments'] = array($objdata->attachment1);
        if (isset($objdata->attachment2->filevalue))
            array_push($data['attachments'], array($objdata->attachment2));   

        //per la registraizone si usa la stessa funzione dell'updateSottoscrizione
        $conv = $this->convenzioneRepository->updateSottoscrizioneStep($data);   
        
        //chiudere i task associati ... di tipo sottoscrizione
        //$tasks = $conv->usertasks()->where('workflow_place',Convenzione::APPROVATO)->whereNull('workflow_transition')->get();      
        $tasks = $conv->usertasks()->where('workflow_place',Convenzione::APPROVATO)->get();

        if ($conv->stipula_type == 'controparte'){
            $transition = 'firma_da_controparte1';
        }else{
            $transition = 'firma_da_direttore1';
        }

        //registro nel task la transizione eseguita
        foreach ($tasks as $task) {
            $task->workflow_transition = $transition; 
        }

        $this->closeTasks($tasks);
        
        $this->openTaskCompletamentoSottoscrizione($conv);

        $response = [
            "status" => "success", 
            "message"=> "Registrazione sottoscrizione eseguita con successo. \n",                
            "data" => $conv
        ];

        return $response;

    }

    public function updateSottoscrizioneStep($request){                
        
        $data = Sottoscrizione::exec($request);

        $conv = $this->convenzioneRepository->updateSottoscrizioneStep($data);   
         //chiudere i task associati ... di tipo sottoscrizione
        //$tasks = $conv->usertasks()->where('workflow_place',Convenzione::APPROVATO)->whereNull('workflow_transition')->get();
        $tasks = $conv->usertasks()->where('workflow_place',Convenzione::APPROVATO)->get();
        $this->closeTasks($tasks);
      
        $this->openTaskCompletamentoSottoscrizione($conv);

        $nprot = '';
        if (array_key_exists('attachments',$data) && count($data['attachments'])>0){
            $attach = $data['attachments'][0];
            if (array_key_exists('num_prot',$attach))
                $nprot = $attach['num_prot'];
        }

        $response = [
            "status" => "success", 
            "message"=> "Sottoscrizione eseguita con successo. \r\n ".
                    ($nprot ? "Numero di protocollo: ".$nprot : '')."\r\n".
                    ($conv->numero ? "Numero di fascicolo: ".$conv->numero : ''),
            "data" => $conv];

        return $response;
    }    
      
    public function registrazioneComplSottoscrizione($request){

        $data = RegistrazioneCompletamentoSottoscrizione::exec($request);
        $conv = $this->convenzioneRepository->updateComplSottoscrizioneStep($data);  

        //chiudere i task associati ... di tipo sottoscrizione
        $tasks = $conv->usertasks()->where('workflow_transition', $request->transition)->get();
        $this->closeTasks($tasks); 
        
        $task = (new GenericTask($conv))
        ->owner($conv->user_id)            
        ->workflow_place($conv->current_place)
        ->workflow_transition($conv->workflow_transitions()[0]->getName())
        ->subject('Apposizione bollo e repertoriazione')->description('Apposizione bollo e repertoriazione convenzione');

        $task->save();

        $response = [
            "status" => "success", 
            "message"=> "Registrazione completamento sottoscrizione eseguita con successo. \n",                
            "data" => $conv
        ];

        return $response;
    }


    public function updateComplSottoscrizioneStep($request){        
        
        $data = CompletamentoSottoscrizione::exec($request);

        $conv = $this->convenzioneRepository->updateComplSottoscrizioneStep($data);  
        //chiudere i task associati ... di tipo sottoscrizione
        $tasks = $conv->usertasks()->where('workflow_transition', $request->transition)->get();
        $this->closeTasks($tasks); 
                
      
        $task = (new GenericTask($conv))
            ->owner($conv->user_id)            
            ->workflow_place($conv->current_place)
            ->workflow_transition($conv->workflow_transitions()[0]->getName())
            ->subject('Apposizione bollo e repertoriazione')->description('Apposizione bollo e repertoriazione convenzione');

        $task->save();

        $nprot = '';
        if (array_key_exists('attachments',$data) && count($data['attachments'])>0){
            $attach = $data['attachments'][0];
            if (array_key_exists('num_prot',$attach))
                $nprot = $attach['num_prot'];
        }

        $response = [
            "status" => "success", 
            "message"=> "Sottoscrizione completata con successo. \r\n".
                ($nprot ? "Numero di protocollo: ".$nprot : '')."\r\n".
                ($conv->numero ? "Numero di fascicolo: ".$conv->numero : ''),
            "data" => $conv];

        return $response;

        //return $conv;
     
    }

    public function registrazioneBolloRepertoriazione($request){

        $data = RegistrazioneRepertoriazione::exec($request);

        $conv = $this->convenzioneRepository->updateBolloRepertoriazioneStep($data);  
        //chiudere i task associati ... di tipo sottoscrizione
        $tasks = $conv->usertasks()->where('workflow_transition', 'repertorio')->get();
        $this->closeTasks($tasks);                       
        
        $response = [
            "status" => "success", 
            "message"=> "Registrazione convenzione repertoriata eseguita con successo. \n",                
            "data" => $conv
        ];
      
        return $response;
    }

    public function updateBolloRepertoriazioneStep($request){

        $data = Repertoriazione::exec($request);

        $conv = $this->convenzioneRepository->updateBolloRepertoriazioneStep($data);  
        //chiudere i task associati ... di tipo sottoscrizione
        $tasks = $conv->usertasks()->where('workflow_transition', 'repertorio')->get();
        $this->closeTasks($tasks);                 

        if ($conv->num_rep){                
            //notifica al possessore della convenzione dell'avvenuta repertoriazione
            try {
                //la notifica va fatta a chi ha aperto il task che coincide con quello che ha creato la convenzione
                $user = User::find($conv->user_id);
                $user->notify(new ConvenzioneRepertoriata($conv));    
            } catch (Exception $e) {
                Log::error('Notifica repertoriazione non inviata utente [' .$conv->user_id. ']');       
            }
        }

        //aprire un nuovo task per convservazione        
        $nrep = '';
        if (array_key_exists('attachments',$data) && count($data['attachments'])>0){
            $attach = $data['attachments'][0];
            if (array_key_exists('num_rep',$attach))
                $nrep = $attach['num_rep'];
        }

        $response = [
            "status" => "success", 
            "message"=> "Repertoriazione completata con successo. \r\n ".
                ($nrep ? "Numero di repertorio: ".$nrep : '')."\r\n". 
                ($conv->numero ? "Numero di fascicolo: ".$conv->numero : ''),
            "data" => $conv];

        return $response;
    }

    public function updateRichiestaEmissioneStep($request){
        
        $data = $request->all();        

        $scad = $this->convenzioneRepository->updateRichiestaEmissione($data);
        //inviare notifica agli assegnatari

        foreach ($data['assignments'] as $key => $value ) {
            //notifica al possessore dell'avvenuto invio della richiesta
            //la notifica va fatta a chi è stato assegnato il task
            //TUTTI gli assegnatari devono essere registrati...
            //se l'utente non c'è ... registralo
            $toUser = User::where('v_ie_ru_personale_id_ab',$value['v_ie_ru_personale_id_ab'])->first();                
            if ($toUser==null){      
                $toUser = $this->addUser($value['v_ie_ru_personale_id_ab']);                              
            }
                    
            //email e notifica di richiesta emissione
            $toUser->notify(new RichiestaEmissione($scad, $data, ['database','mail'], $toUser));    
            //notifica di emessa richiesta emissione
            Auth::user()->notify(new RichiestaEmissione($scad, $data, ['database'], $toUser));                
        }
        
        return $scad;
    }    

    public function addUser($id_ab){
        //registro l'utente                                    
        $pers = Personale::FindByIdAB($id_ab)->first();                         
        $data = LoginService::roleAndData($pers); 

        if ($data){                                                            
            $user = new \App\User;                             
            $user->name = $this->onlyFirstUpper((string)$pers->nome).' '.$this->onlyFirstUpper((string)$pers->cognome);
            $user->email = $pers->email;
            $user->password = Hash::make($pers->cod_fis);   
            $user->v_ie_ru_personale_id_ab = $data['id_ab'];
            $user->save();                       
            $user->assignRole($data['ruoli']);                                  
        }

        return $user;
    }


    public function updateInvioRichiestaPagamentoStep($request){
        $data = $request->all();        
        $objdata = (object) $data;

        $conv = Convenzione::findOrFail($data['convenzione_id']);    
        $aziende = $conv->aziende()->get();
        $tipodoc = Documento::PARTENZA;        

        //protocollare
        $data['attachments'] = TitulusHelper::saveDocumentInTitulus('UniConv '.$conv->descrizione_titolo, array($data['attachment1']), 
            $tipodoc, $aziende, $conv, $conv->nrecord, true);                    

        //inviare email 
        TitulusHelper::sendDocumentByEmail($data['attachments'][0]['nrecord']);

        //salvataggio allegato e transizione del workflow        
        $scad = $this->convenzioneRepository->updateInvioRichiestaPagamento($data);  
        
        //vado direttamente al task inpagamento
        $scad->workflow_apply(Scadenza::ORDINEINCASSO, $scad->getWorkflowName());        
        $scad->save();

        //aprire un task IN PAGAMENTO per quando la convenzione è stata pagata
        $task = (new InPagamentoTask($scad))           
            ->subject('Scadenza in pagamento')->description('Scadenza in pagamento');

        $task->save();

        $nprot = '';
        if (array_key_exists('attachments',$data) && count($data['attachments'])>0){
            $attach = $data['attachments'][0];
            if (array_key_exists('num_prot',$attach))
                $nprot = $attach['num_prot'];
        }

        $response = [
            "status" => "success", 
            "message"=> "Inviata richiesta di pagamento. \r\n".
                ($nprot ? "Numero di protocollo: ".$nprot : '')."\r\n".
                ($conv->numero ? "Numero di fascicolo: ".$conv->numero : ''),
            "data" => $scad];

        return $response;        
    }


    public function updateEmissioneStep($request){
        
        $data = $request->all();        
        $objdata = (object) $data;
        //ricezione pec
        if ($objdata->attachment1['attachmenttype_codice'] == 'FATTURA_ELETTRONICA' || $objdata->attachment1['attachmenttype_codice'] == 'NOTA_DEBITO' ){            
            $attach['attachmenttype_codice'] = $objdata->attachment1['attachmenttype_codice'];
            
            $doc = $objdata->attachment1['doc'];
            if (array_key_exists('nrecord',$doc))
                $attach['nrecord'] = $doc['nrecord'];            
            if (array_key_exists('num_prot',$doc))
                $attach['num_prot'] = $doc['num_prot'];

            $attach['emission_date'] =  $doc['data_prot']; 
            $data['attachment1'] = $attach;
        }

        $scad = $this->convenzioneRepository->updateEmissione($data);        

        //chiudere i task associati ... di tipo inemissione
        $tasks = $scad->usertasks()->where('workflow_transition', Scadenza::EMISSIONE)->get();
        $this->closeTasks($tasks);         

        $scad->workflow_apply(Scadenza::ORDINEINCASSO, $scad->getWorkflowName());        
        $scad->save();


        //notifica al possessore della convenzione dell'avvenuta approvazione
        try {
            //la notifica va fatta a chi ha aperto il task che coincide con quello che ha creato la convenzione
            $user = User::find($scad->convenzione->user_id);
            $user->notify(new ScadenzaEmessa($scad));    
        } catch (Exception $e) {
            Log::error('Notifica non inviata utente [' .$scad->convenzione->user_id. ']');       
        }

        //aprire un task IN PAGAMENTO per quando la convenzione è stata pagata
        $task = (new InPagamentoTask($scad))           
            ->subject('Scadenza in pagamento')->description('Scadenza in pagamento');

        $task->save();

        return $scad;
    }

    public function updateModificaEmissioneStep($request){
        
        $data = $request->all();        
        $objdata = (object) $data;

        if ($objdata->attachment1['attachmenttype_codice'] == 'FATTURA_ELETTRONICA' || $objdata->attachment1['attachmenttype_codice'] == 'NOTA_DEBITO' ){            

            $attach['attachmenttype_codice'] = $objdata->attachment1['attachmenttype_codice'];            
            $doc = $objdata->attachment1['doc'];
            if (array_key_exists('nrecord',$doc))
                $attach['nrecord'] = $doc['nrecord'];            
            if (array_key_exists('num_prot',$doc))
                $attach['num_prot'] = $doc['num_prot'];

            $attach['emission_date'] =  $doc['data_prot']; 
            $data['attachment1'] = $attach;
        }

        $scad = $this->convenzioneRepository->updateModificaEmissione($data);        

        return $scad;
    }



    public function updatePagamentoStep($request){
        $data = $request->all();        
        $objdata = (object) $data;

        $scad = $this->convenzioneRepository->updatePagamento($data);   

        //chiudere i task associati ... di tipo registrazione pagamento
        $tasks = $scad->usertasks()->where('workflow_transition', Scadenza::REGISTRAZIONEPAGAMENTO)->get();
        $this->closeTasks($tasks);   
        
        return $scad;
    }

    private function onlyFirstUpper($value){
        return ucwords(strtolower($value));
    }

    public function closeTasks($tasks){
        foreach ($tasks as $task) {
            if($task->workflow_can(UserTask::STORE_ESEGUITO,'usertask')){
                $task->workflow_apply(UserTask::STORE_ESEGUITO,'usertask');
                $now = new DateTime();
                $task->description = $task->description.' Completato da '.Auth::user()->name.' in data '.$now->format('d-m-Y');
                $task->closing_user_id = Auth::user()->id;
                $task->save();
            }        
        }
    }

    public function annullaTasks($tasks){
        foreach ($tasks as $task) {
            if($task->workflow_can(UserTask::STORE_CONERRORI,'usertask')){
                $task->workflow_apply(UserTask::STORE_CONERRORI,'usertask');
                $now = new DateTime();
                $task->description = $task->description.' Completato da '.Auth::user()->name.' in data '.$now->format('d-m-Y');
                $task->closing_user_id = Auth::user()->id;
                $task->save();
            }        
        }
    }

}