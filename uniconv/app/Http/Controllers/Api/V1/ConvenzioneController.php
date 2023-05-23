<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Repositories\ConvenzioneRepository;
use App\Service\ConvenzioneService;
use App\Http\Controllers\Controller;
use App\Convenzione;
use App\Permission;
use App\UserTask;
use App\Scadenza;
use PDF;
use Response;
use Validator;
use Storage;
use App\Http\Resources\PersonaleResource;
use App\Http\Resources\WorkflowConvenzione;
use App\Http\Resources\WorkflowConvenzioneSchemaTipoResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use App\Service\TitulusHelper;
use Auth;
use App\Service\UtilService;
use App\Exports\ConvenzioniExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Notifications\ConvenzioneCancellata;
use App\User;

//generazione documentazione api
//php artisan api:generate --routePrefix="api/v1/*"

//generazione controller api
//php artisan make:controller ConvezioneController --api

class ConvenzioneController extends Controller
{

    /**
     * @var ConvezioneRepository
     */
    private $convenzioneRepo;

    /**
     * @var ConvezioneService
     */
    private $convenzioneService;


    /**
     * BusinessService constructor.
     * @param ConvezioneRepository $convenzioneRepo
     * @param ConvezioneService $convenzioneService
     */
    public function __construct(ConvenzioneRepository $repo, ConvenzioneService $service)    
    {
        $this->convenzioneRepo = $repo;
        $this->convenzioneService = $service;
    }



    /**
     * Non implementato -- Crea un oggetto nuova convenzione  
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Non implementato
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->convenzioneRepo->paginate(25);
        //return Convezione::with('dipartimenti','referentiCC')->paginate(25);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {               

        $this->validate($request, [
            'titolario_classificazione'=>'required',
            'oggetto_fascicolo'=>'required',
            ]
        );

        if ($request->id){      
            if (!Auth::user()->hasPermissionTo('update convenzioni')) {
                abort(403, trans('global.utente_non_autorizzato'));
            }
            //aggiorna
            return $this->update($request->all(), $request->id);            
        }
        
        if (!Auth::user()->hasPermissionTo('create convenzioni')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }
        //crea nuovo
        $conv = $this->convenzioneService->createConvenzione($request);    

        return response()->json($conv, 200);  
    }

    public function createSchemaTipo(Request $request)
    { 
        if (!Auth::user()->hasPermissionTo('create convenzioni')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        //verifiche allegati       
        $conv = $this->convenzioneService->createConvenzione($request);

        return response()->json($conv, 200);          
    }

    public function updateValidationStep(Request $request){
        
        if (!Auth::user()->hasPermissionTo('store_validazione convenzioni')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        //validazione
        $conv = $this->convenzioneService->updateValidationStep($request);

        return response()->json($conv, 200);          
    }


    public function cancellazioneSottoscrizione(Request $request){
        
        if (!Auth::user()->hasAnyPermission(['cancella_sottoscrizione_contr convenzioni', 'cancella_sottoscrizione_uniurb convenzioni'])) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        // leggi la convenzione e verifica lo stato
        $entity = Convenzione::findOrFail($request->id);       
        if (!($entity->workflow_can('cancella_sottoscrizione_contr', $entity->getWorkflowName()) 
                || $entity->workflow_can('cancella_sottoscrizione_uniurb', $entity->getWorkflowName()))){
            // stato convenzione non consentito per questa operazione
            abort(500, trans('global.conv_stato_non_valido'));
        }

        //registrazione sottoscrizione
        $msg = $this->convenzioneService->cancellazioneSottoscrizione($request);

        return response()->json($msg, 200);          

    }


    public function registrazioneSottoscrizione(Request $request){
        
        if (!Auth::user()->hasAnyPermission(['firma_da_controparte1 convenzioni', 'firma_da_direttore1 convenzioni'])) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        // leggi la convenzione e verifica lo stato
        $entity = Convenzione::findOrFail($request->convenzione_id);       
        if (!($entity->workflow_can('firma_da_direttore1', $entity->getWorkflowName()) 
                || $entity->workflow_can('firma_da_controparte1', $entity->getWorkflowName()))){
            // stato convenzione non consentito per questa operazione
            abort(500, trans('global.conv_stato_non_valido'));
        }

        //registrazione sottoscrizione
        $conv = $this->convenzioneService->registrazioneSottoscrizione($request);

        return response()->json($conv, 200);          

    }
    
    
    public function updateSottoscrizioneStep(Request $request){
        
        if (!Auth::user()->hasAnyPermission(['firma_da_controparte1 convenzioni', 'firma_da_direttore1 convenzioni'])) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $this->validate($request, [
            'convenzione_id'=>'required',           
            ]
        );

        // leggi la convenzione e verifica lo stato
        $entity = Convenzione::findOrFail($request->convenzione_id);       
        if (!($entity->workflow_can('firma_da_direttore1', $entity->getWorkflowName()) 
                || $entity->workflow_can('firma_da_controparte1', $entity->getWorkflowName()))){
            // stato convenzione non consentito per questa operazione
            abort(500, trans('global.conv_stato_non_valido'));
        }

        //sottoscrizione
        $conv = $this->convenzioneService->updateSottoscrizioneStep($request);

        return response()->json($conv, 200);          
    }
    

    public function registrazioneComplSottoscrizione(Request $request){
        
        if (!Auth::user()->hasAnyPermission(['firma_da_controparte2 convenzioni', 'firma_da_direttore2 convenzioni'])) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $this->validate($request, [
            'convenzione_id'=>'required',           
            ]
        );

        // leggi la convenzione e verifica lo stato
        $entity = Convenzione::findOrFail($request->convenzione_id);       
        if (!($entity->workflow_can('firma_da_controparte2', $entity->getWorkflowName()) 
                || $entity->workflow_can('firma_da_direttore2', $entity->getWorkflowName()))){
            // stato convenzione non consentito per questa operazione
            abort(500, trans('global.conv_stato_non_valido'));
        }
  
        //completamento sottoscrizione
        $conv = $this->convenzioneService->registrazioneComplSottoscrizione($request);

        return response()->json($conv, 200);        

    }

    public function updateComplSottoscrizioneStep(Request $request){
        
        if (!Auth::user()->hasAnyPermission(['firma_da_controparte2 convenzioni', 'firma_da_direttore2 convenzioni'])) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $this->validate($request, [
            'convenzione_id'=>'required',           
            ]
        );

        // leggi la convenzione e verifica lo stato
        $entity = Convenzione::findOrFail($request->convenzione_id);       
        if (!($entity->workflow_can('firma_da_controparte2', $entity->getWorkflowName()) 
                || $entity->workflow_can('firma_da_direttore2', $entity->getWorkflowName()))){
            // stato convenzione non consentito per questa operazione
            abort(500, trans('global.conv_stato_non_valido'));
        }

        //completamento sottoscrizione
        $conv = $this->convenzioneService->updateComplSottoscrizioneStep($request);

        return response()->json($conv, 200);          
    }

    public function registrazioneBolloRepertoriazione(Request $request){
        if (!Auth::user()->hasPermissionTo('repertorio convenzioni')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $this->validate($request, [
            'convenzione_id'=>'required',  
            'attachment1'=>'required',            
            ]
        );

        // leggi la convenzione e verifica lo stato
        $entity = Convenzione::findOrFail($request->convenzione_id);       
        if (!($entity->workflow_can('repertorio', $entity->getWorkflowName()))) {
            // stato convenzione non consentito per questa operazione
            abort(500, trans('global.conv_stato_non_valido'));
        }

        //completamento sottoscrizione
        $conv = $this->convenzioneService->registrazioneBolloRepertoriazione($request);

        //TODO - rispondere con numero di repertorio        
        return response()->json($conv, 200);          

    }

    public function updateBolloRepertoriazioneStep(Request $request){
        
        if (!Auth::user()->hasPermissionTo('repertorio convenzioni')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $this->validate($request, [
            'convenzione_id'=>'required',  
            'attachment1'=>'required',            
            ]
        );

        // leggi la convenzione e verifica lo stato
        $entity = Convenzione::findOrFail($request->convenzione_id);       
        if (!($entity->workflow_can('repertorio', $entity->getWorkflowName()))) {
            // stato convenzione non consentito per questa operazione
            abort(500, trans('global.conv_stato_non_valido'));
        }

        //completamento sottoscrizione
        $conv = $this->convenzioneService->updateBolloRepertoriazioneStep($request);

        //TODO - rispondere con numero di repertorio        
        return response()->json($conv, 200);          
    }

    public function updateRichiestaEmissioneStep(Request $request){
             
        if (!Auth::user()->hasPermissionTo('richiestaemissione scadenze')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $this->validate($request, [
            'id' => 'required',
            'tipo_emissione'=>'required',            
            ]
        );

        $entity = Scadenza::findOrFail($request->id);       
        if (!($entity->workflow_can('richiestaemissione', $entity->getWorkflowName()))) {            
            abort(500, trans('global.scad_stato_non_valido'));
        }        

        $scad = $this->convenzioneService->updateRichiestaEmissioneStep($request);

        return response()->json($scad, 200);          
    }

    public function updateInvioRichiestaPagamentoStep(Request $request){
                     
        if (!Auth::user()->hasPermissionTo('richiestapagamento scadenze')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $this->validate($request, [
            'id' => 'required',          
            ]
        );

        $entity = Scadenza::findOrFail($request->id);       
        if (!($entity->workflow_can('richiestapagamento', $entity->getWorkflowName()))) {            
            abort(500, trans('global.scad_stato_non_valido'));
        }    

        $scad = $this->convenzioneService->updateInvioRichiestaPagamentoStep($request);

        return response()->json($scad, 200);          
    }    

    public function updateEmissioneStep(Request $request){
             
        if (!Auth::user()->hasPermissionTo('emissione scadenze')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $this->validate($request, [
            'id' => 'required'  
            ]
        );

        $entity = Scadenza::findOrFail($request->id);       
        if (!($entity->workflow_can('emissione', $entity->getWorkflowName()))) {            
            abort(500, trans('global.scad_stato_non_valido'));
        }    

        $scad = $this->convenzioneService->updateEmissioneStep($request);

        return response()->json($scad, 200);          
    }


    public function updateModificaEmissioneStep(Request $request){
             
        if (!Auth::user()->hasPermissionTo('emissione scadenze')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $this->validate($request, [
            'id' => 'required',          
            ]
        );

        $entity = Scadenza::findOrFail($request->id);       
        if (!in_array($entity->state,['emesso', 'inpagamento', 'pagato'])) {            
            abort(500, trans('global.scad_stato_non_valido'));
        }    

        $scad = $this->convenzioneService->updateModificaEmissioneStep($request);

        return response()->json($scad, 200);          
    }



    public function updatePagamentoStep(Request $request){
        
        if (!Auth::user()->hasPermissionTo('registrazionepagamento scadenze')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $this->validate($request, [
            'id' => 'required',          
            ]
        );

        $entity = Scadenza::findOrFail($request->id);       
        if (!($entity->workflow_can('registrazionepagamento', $entity->getWorkflowName()))) {            
            abort(500, trans('global.scad_stato_non_valido'));
        }    

        $scad = $this->convenzioneService->updatePagamentoStep($request);

        return response()->json($scad, 200);          
    }


    public function getminimal($id)
    {        
        return Convenzione::with(['aziende'])->where('id',$id)->first();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        if (!Auth::user()->hasPermissionTo('view convenzioni')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        //se è un docente solo quelle di afferenza 
        //se è op_contabile può vedere solo quelle a titolo oneroso ... per cui è stata richiesta una emissione ... 
        //se viewer solo quelle di afferenza organizzativa        

        $conv = $this->convenzioneRepo->findById($id);
       
        if ($conv==null){
            return response()->json(['error'=>"Non trovato"], 404);
        }

        $this->autorizzazioneShowConvenzione($conv);
  
        return $conv;
    }

    private function autorizzazioneShowConvenzione($conv)
    {
        //se l'utente non ha il peremesso 'search all convenzioni' va filtrato 
        if (!Auth::user()->hasPermissionTo('search all convenzioni')){
            //aggiungere controllo per unitaorganizzativa_uo dell'utente che esegue la richiesta ...
            $uo = Auth::user()->unitaorganizzativa();
            $id_ab = Auth::user()->v_ie_ru_personale_id_ab;
            if ($uo->isDipartimento()){    
                //caso docenti che va ulteriormente filtrato ... 
                //ad un afferente al dipartimento filtro per dipartimento       
                $dip = $uo->dipartimento()->first();    
                if (!in_array($conv->dipartimemto_cd_dip,[$dip->cd_dip])){
                    abort(403, trans('global.utente_non_autorizzato')); 
                }   
            }else if ($uo->isPlesso()){                 
                if (!in_array($conv->dipartimemto_cd_dip, $uo->dipartimenti_cd_dip())){
                    abort(403, trans('global.utente_non_autorizzato')); 
                }  
            }else if (Auth::user()->hasRole('op_contabilita')){                                
                if (!(in_array($uo->uo, $conv->scadenzeusertasks()->pluck('unitaorganizzativa_uo')->toArray()) || 
                        in_array($id_ab, $conv->scadenzeusertasks()->pluck('respons_v_ie_ru_personale_id_ab')->toArray()))
                    ){
                    abort(403, trans('global.utente_non_autorizzato')); 
                } 
            }else if (Auth::user()->hasRole('op_approvazione')){
                //op_approvazione                                
                //o sei nell'ufficio o sei il responsabile
                if (!(in_array($uo->uo, $conv->usertasks()->pluck('unitaorganizzativa_uo')->toArray()) || 
                        in_array($id_ab, $conv->usertasks()->pluck('respons_v_ie_ru_personale_id_ab')->toArray()))
                    ){
                    abort(403, trans('global.utente_non_autorizzato')); 
                } 
            }else{
                //filtro per unitaorganizzativa dell'utente di inserimento (ufficio)
                //se l'utente è responsabile di più uffici, filtro per unitaorganizzativa di ogni ufficio
                $uos = Auth::user()->codiciUnitaorganizzative();
                if (!in_array($conv->unitaorganizzativa_uo, $uos)){
                    abort(403, trans('global.utente_non_autorizzato')); 
                }  
            }          
        }
    }

    public function nextPossibleActions($id){
        return $this->convenzioneService->nextPossibleActions($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        if (!Auth::user()->hasPermissionTo('update convenzioni')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $validator = Validator::Make($request->all(), []);

        if ($validator->fails()) { 
            return response()->json($validator->errors(), 401);
        }

        $conv = $this->convenzioneRepo->findById($id);
        if ($conv==null){
            return response()->json(['error'=>"Non trovato"], 404);
        }

        if (!Auth::user()->hasRole('super-admin')){
            if ($request->convenzione_from == 'dip'){                       
                //se è una convenzione dipartimentale, controllo che l'utente afferisca al dipartimento o al plesso
                $uo = Auth::user()->unitaorganizzativa();
                if ($uo->isDipartimento()){
                    $dip = $uo->dipartimento()->first();
                    if ($dip->cd_dip != $conv->dipartimemto_cd_dip){
                        abort(403, trans('global.utente_non_autorizzato').' [unità organizzativa non consistente]');
                    }  
                }else if ($uo->isPlesso()){
                    if (!in_array($conv->dipartimemto_cd_dip, $uo->dipartimenti_cd_dip())){
                        abort(403, trans('global.utente_non_autorizzato').' [unità organizzativa non consistente]');
                    }
                }else{
                    abort(403, trans('global.utente_non_autorizzato'));
                }
            } else {
                //se è una convenzione amministrativa, controllo che l'utente afferisca alla convenzione
                $uos = Auth::user()->codiciUnitaorganizzative();
                if (!in_array($conv->unitaorganizzativa_uo, $uos)){
                    abort(403, trans('global.utente_non_autorizzato').' [unità organizzativa non consistente]');
                }                
            }
        }
                      
        $conv = $this->convenzioneRepo->update($request->all(), $id);        
        $conv = $this->convenzioneRepo->findById($id);

       
        return response()->json($conv, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function annullaConvenzione(Request $request)
    {
        $this->validate($request, [
            'id'=>'required',
            'entity'=>'required'
            ]
        );

        if (!Auth::user()->hasPermissionTo('update convenzioni')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        Convenzione::where('id',$request->id)->delete();
        $conv = $this->convenzioneRepo->findById($request->id);

        //chiudere i task associati ... a questa convenzione in stato ... a approvato
        $tasks = $conv->usertasks()->get();
        $this->convenzioneService->annullaTasks($tasks);

        $user = User::find($conv->user_id);
        $user->notify(new ConvenzioneCancellata($conv,$request->entity['note']['motivazione']));    

        $success = true;
        $message = '';     
        $data = $conv;
        return compact('data', 'message', 'success');
    }    


    public function queryparameter(Request $request){
        //NB lettura parametri con json() per test exportCsv
        $parameters = $request->json()->all();

        $parameters['includes'] = 'aziende,tipopagamento,bolli';
        $parameters['columns'] =   implode (",", ['id',
            'descrizione_titolo',
            'user_id',
            'schematipotipo',
            'dipartimemto_cd_dip',
            'resp_scientifico',
            'ambito',
            'durata',        
            'prestazioni',
            'corrispettivo',        
            'importo',
            'tipopagamenti_codice',
            'current_place',
            'unitaorganizzativa_uo',
            'convenzione_type',
            'stipula_type',
            'titolario_classificazione',
            'oggetto_fascicolo',
            'nrecord',
            'numero',
            'num_rep',
            'data_sottoscrizione',
            'data_inizio_conv',
            'data_fine_conv',
            'data_stipula',
            'aziende.id',
            'aziende.denominazione',
            'tipopagamento.codice',
            'tipopagamento.descrizione',
            'rinnovo_type',   
            'bollo_virtuale',       
            'bolli.convenzioni_id',
            'bolli.tipobolli_codice',
            'bolli.num_bolli',
            'bolli.num_righe'
        ]);

        $parameters = $this->filtriPermessiRicercaConvenzioni($parameters);
    
        return new \App\FindParameter($parameters);
    }

    private function filtriPermessiRicercaConvenzioni($parameters)
    {
        //se l'utente non ha il peremesso 'search all convenzioni' va filtrato 
        if (!Auth::user()->hasPermissionTo('search all convenzioni')){
            //aggiungere filtro per unitaorganizzativa_uo
            $uo = Auth::user()->unitaorganizzativa();
            if ($uo->isDipartimento()){
                //ad un afferente al dipartimento filtro per dipartimento
                $dip = $uo->dipartimento()->first();
                array_push($parameters['rules'],[
                    "operator" => "=",
                    "field" => "dipartimemto_cd_dip",                
                    "value" => $dip->cd_dip
                ]);
            }else if ($uo->isPlesso()){
                //filtro per unitaorganizzativa dell'utente di inserimento (plesso)
                array_push($parameters['rules'],[
                    "operator" => "In",
                    "field" => "dipartimemto_cd_dip",                
                    "value" => $uo->dipartimenti_cd_dip()
                ]);
            }else{
                //filtro per unitaorganizzativa dell'utente di inserimento (ufficio)
                //se l'utente è responsabile di più uffici, filtro per unitaorganizzativa di ogni ufficio
                $uos = Auth::user()->codiciUnitaorganizzative();
                array_push($parameters['rules'],[
                    "operator" => "In",
                    "field" => "unitaorganizzativa_uo",                
                    "value" => $uos
                ]);
            }                                            
        }        

        return $parameters;
    }

    public function query(Request $request){
        
        //permesso search all convenzioni 
        if (!Auth::user()->hasAnyPermission(['search all convenzioni', 'search orgunit convenzioni'])) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $findparam = $this->queryparameter($request);
        $queryBuilder = new QueryBuilder(new Convenzione, $request, $findparam);

        return $queryBuilder->build()->paginate();        
    }

    public function export(Request $request){
        //prendi i parametri 
        $findparam = $this->queryparameter($request);          
        return (new ConvenzioniExport($request,$findparam))->download('convenzioni.csv', \Maatwebsite\Excel\Excel::CSV,  [
            'Content-Type' => 'text/csv',
        ]);        
    }

    public function exportxls(Request $request){
        //prendi i parametri 
        $findparam = $this->queryparameter($request);                              
        return (new ConvenzioniExport($request,$findparam))->download('convenzioni.xlsx');     
    }


    public function pagamenti(Request $request)
    {
        return \App\TipoPagamento::all();
    }

    public function classificazioni(Request $request)
    {
        return \App\Classificazione::all();
    }

    public function attachemnttypes(Request $request)
    {
        return \App\AttachmentType::where('parent_type',Convenzione::class)->get();
    }

    public function uffici($id)    
    {
        if ($id=='validazione')
            return \App\UnitaOrganizzativa::UfficiValidazione()->get();
        if ($id=='inemissione')
            return \App\UnitaOrganizzativa::UfficiFiscali()->get();
        if ($id=='tutti')
            return \App\UnitaOrganizzativa::UfficiRuoli()->get();

    }        

    public function personaleUfficio($id)
    {
        PersonaleResource::withoutWrapping();
        return PersonaleResource::collection(\App\PersonaleResponsOrg::FindByAfferenzaOrganizzativa($id));
    }    


    // public function validationResponsabileUfficio($id)
    // {
    //     PersonaleResource::withoutWrapping();
    //     return PersonaleResource::collection(\App\PersonaleResponsOrg::FindByAfferenzaOrganizzativa($id)->Respons()->get());
    // }    


    private function pdfFromView($dataarray){
        $pdf = PDF::loadView('convenzione',  $dataarray)
            ->setOption('encoding', 'utf-8')
            ->setOption('margin-left','20')
            ->setOption('margin-right','20')
            ->setOption('margin-top','30')
            ->setOption('margin-bottom','20');          
        return $pdf;
    }

    //generazione del pdf da template blade
    public function generatePDF($id){
        $conv = $this->convenzioneRepo->findById($id)->toArray();
        $pdf = $this->pdfFromView($conv);
    
        return $pdf->download(); 
    }

    //generazione del pdf da template blade
    public function generatePostPDF(Request $request)
    {  
        $pdf = $this->pdfFromView($request->all());
        return $pdf->download();
    }

    
    public function uploadPDF(Request $request)
    {          
        if ($request->convenzione_pdf && $request->id){
            $file = base64_decode($request->convenzione_pdf['value']);
            Storage::put($this->getNameConvezione($request->id), $file); 
            return  response()->json(['message'=>'La convenzione è stata memorizzata con successo'], 200);
        }
        return response()->json(['error'=>'La convenzione non è stata memorizzata'], 404);
    }


    public function getNameConvezione($id){
        return 'convenzioni/'.$id.'_convenzione.pdf';
    }
    
    public function downloadAttachment($id){
        //todo istanziare il controller attachment
        $attach = Attachment::find($id);
        if ($attach->num_prot){
            $app = TitulusHelper::downloadAttachment($attach->num_prot,$attach->filename);
            if ($app){

                if ($attach->attachmenttype_codice=="FATTURA_ELETTRONICA" && ($app->mimeType == "application/xml" || $app->mimeType == "application/octet-stream")){
                    $pdf = TitulusHelper::createFatturaPA($app->content);
                    $attach['filevalue'] = base64_encode($pdf->output());
                    if ($attach->filetype == 'link'){
                        $attach['filename'] = $app->title.'.pdf';
                    }                
                }else{

                    $attach['filevalue'] =  base64_encode($app->content);                                                    
                    if ($attach->filetype == 'link'){
                        $attach['filename'] = $app->title.'.pdf';
                    }
                }                
            }
        }else{
            $attach['filevalue'] =  base64_encode(Storage::get($attach->filepath));
        }        
        return $attach;    
    }
   
    
    public function getTitulusDocumentURL($id){
        return (new AttachmentController())->getTitulusDocumentURL($id);
    }

    public function getAziende($id){
        $aziende = Convenzione::find($id)->aziende()->get();
        return $aziende;
    }
}
