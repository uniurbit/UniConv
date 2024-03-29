<?php namespace App\Repositories;
 
use App\Repositories\Events\RepositoryEntityUpdated;
use App\Repositories\RepositoryInterface;
use App\Repositories\Repository;
use Illuminate\Support\Facades\Auth;
use App\Convenzione;
use App\Attachment;
use App\Scadenza;
use App\UserTask;
use App\Service\ConvenzioneService;
use App\Tasks\EmissioneTask;
use App\Tasks\RichiestaEmissioneTask;
use App\Tasks\InvioRichiestaPagamentoTask;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

use Exception;
use DB;
use App\Repositories\Events\RepositoryEntityCreated;
use Carbon\Carbon;
use DateTime;

class ConvenzioneRepository extends BaseRepository {
 
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return 'App\Convenzione';
    }


    /**
     * @param $id convenzione    
     * @return Convenzione
     */
    public function findMinimalById($id)
    {            
        $convenzione = Convenzione::withTrashed()->with(['attachments:id,attachmenttype_codice'])->where('id', $id)->first();                
        return $convenzione;
    }

    /**
     * @param $id convenzione    
     * @return Convenzione
     */
    public function findById($id)
    {            
        $convenzione = Convenzione::withTrashed()->with([
            'user:id,name',
            'tipopagamento',            
            'aziende',
            'attachments', 
            'scadenze',
            'usertasks', 
            'usertasks.assignments.user',
            'logtransitions',       
            'bolli'               
            ])->where('id', $id)->first();               

        if ($convenzione == null){
            return $convenzione;
        }

        if ($convenzione->bollo_virtuale != null && $convenzione->bollo_virtuale == true){
            $convenzione['bollo_atti'] = $convenzione->bolli_atti_prov();
            $convenzione['bollo_allegati'] = $convenzione->bolli_allegato();
        }   

        return $convenzione;
    }

    /**
     * @param $userId     
     * @return Convenzione
     */
    public function findByUserId($userId)
    {                 
        $convenzione = $this->model->withTrashed()->where('user_id', $userId)->first();

        if (!$convenzione) {
            return null;
        }
        return $convenzione;
    }

    
    public function update(array $data, $id, $attribute="id") {

        DB::beginTransaction(); 

        try{
            $model = $this->model->findOrFail($id);                         

            unset($data['dipartimento']);
            unset($data['tipopagamento']);
        
            $model->withUser($data['user']);
            unset($data['user_id']);                                
            $model->update($data);
                               
            if (array_key_exists('aziende',$data)){
                $index=1;
                $new_array = array_reduce($data['aziende'], function ($result, $item) use (&$index) {                    
                    $result[(int)$item['id']] = ['ordine' => $index];
                    $index++;
                    return $result;
                }, array());
                $model->aziende()->sync($new_array);
            }
            
            if ($model->bollo_virtuale){               
                if (array_key_exists('bollo_atti', $data))
                {               
                    $model->bolli()->delete();
                    if ($data['bollo_allegati'] == null){
                        $data['bollo_allegati'] = ['tipobolli_codice' => 'BOLLO_ALLEGATI'];
                    }
                    $model->bolli()->createMany([$data['bollo_atti'], $data['bollo_allegati']]);               
                }                     
            }else{
                $model->bolli()->delete();
            }
            

            $this->resetModel();

            event(new RepositoryEntityUpdated($this, $model));
            
        }
        catch(Exception $e)
        {
            DB::rollback();
            throw $e;
        }

        DB::commit();
        return $model;    
    }

    public function create(array $data){  
        DB::beginTransaction(); 
        try{
                        
            unset($data['dipartimento']);
            unset($data['tipopagamento']);            

            $conv = new Convenzione($data);            
            $conv->withUser(Arr::get($data,'user'));                                   
            $conv->save();

            if (array_key_exists('aziende', $data))
            {
                $index=1;
                foreach ($data['aziende'] as $key => $value) {
                    $conv->aziende()->attach((int)$value['id'],['ordine'=>$index]);
                    $index++;
                }
            }

            if ($conv->bollo_virtuale){
                if (array_key_exists('bollo_atti', $data))
                {               
                    $conv->bolli()->delete();
                    if ($data['bollo_allegati'] == null){
                        $data['bollo_allegati'] = ['tipobolli_codice' => 'BOLLO_ALLEGATI'];
                    }
                    $conv->bolli()->createMany([$data['bollo_atti'], $data['bollo_allegati']]);               
                }     
            }
           
            $conv->current_place = 'bozza';
            $conv->save();

            $conv->workflow_apply('store_proposta', $conv->getWorkflowName());
            $conv->save();
            
            if (array_key_exists('attachments',$data)){
               $this->saveAttachments($data['attachments'], $conv);
            }
           
            $service = new ConvenzioneService($this);
            $data = $service->createFascicolo($data);
            $conv->nrecord = $data['nrecord'];
            $conv->numero = $data['numero']; 
            $conv->save();
            
            event(new RepositoryEntityCreated($this, $conv));
        }
        catch(\Exception $e)
        {
            DB::rollback();
            throw $e;
        }

        DB::commit();
        return $this->findById($conv->id);    
    }


    public function updateValidationStep($data, $transition){
        DB::beginTransaction(); 
        try {
            $model = $this->model->findOrFail($data['convenzione_id']);       
            if (array_key_exists('attachments',$data)){                           
                $this->saveAttachments($data['attachments'], $model, true);
            }else{
                throw new Exception("Nessun file in allegato", 1);                
            }    
            $model->workflow_apply($transition, $model->getWorkflowName());
            $model->save();

            event(new RepositoryEntityUpdated($this, $model));

        } catch (\Exception $e) {
             DB::rollback();
             throw $e;
        }

        DB::commit();
        return $this->findMinimalById($model->id);    
    }


    public function deleteSottoscrizioneStep($data){
        DB::beginTransaction();       
        try {
            $model = $this->model->findOrFail($data['id']);       
            $model->stipula_type = null;
            $model->stipula_format = null;
            $model->data_sottoscrizione = null;

            $model->attachments()->whereIn('attachmenttype_codice',
                ['LTE_FIRM_CONTR_PROT','LTE_FIRM_CONTR','LTU_FIRM_UNIURB_PROT','LTU_FIRM_UNIURB','CONV_FIRM_CONTR','CONV_FIRM_UNIURB'])->delete();

            $transition = 'cancella_sottoscrizione_uniurb';
            if ($model->current_place == 'da_firmare_direttore'){
                $transition = 'cancella_sottoscrizione_contr';
            }

            $model->workflow_apply($transition, $model->getWorkflowName());
            $model->save();

            event(new RepositoryEntityUpdated($this, $model));

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
       }

       DB::commit();       
       return $this->findMinimalById($model->id);    
    }

    /**
     * Prende in ingresso la sottoscrizione aggiorna la convenzione con 
     * i dati stipula_type e stipula_format salva gli allegati        
     */
    public function updateSottoscrizioneStep($data){
        DB::beginTransaction();     
        try {
            $model = $this->model->findOrFail($data['convenzione_id']);       
            $model->stipula_type = $data['stipula_type'];
            $model->stipula_format = $data['stipula_format'];

            if (array_key_exists('data_sottoscrizione',$data)){
                $model->data_sottoscrizione = $data['data_sottoscrizione'];
            }else{                
                $model->data_sottoscrizione = Carbon::now()->format(config('unidem.date_format'));
            }

            if (array_key_exists('attachments',$data)){
                //salvati in locale i file protocollati e non 
                $this->saveAttachments($data['attachments'], $model);
            }else{               
                Log::info('[Sottoscrizione] Nessun file in allegato');   
            }    

            $transition = 'firma_da_direttore1';
            if ($model->stipula_type == 'controparte'){
                $transition = 'firma_da_controparte1';
            }

            $model->workflow_apply($transition, $model->getWorkflowName());
            $model->save();

            event(new RepositoryEntityUpdated($this, $model));

        } catch (\Exception $e) {
             DB::rollback();
             throw $e;
        }

        DB::commit();
        return $this->findMinimalById($model->id);    
    }

    public function updateComplSottoscrizioneStep($data){
        DB::beginTransaction();        
        try {
            $model = $this->model->findOrFail($data['convenzione_id']);                   
            
            if (array_key_exists('data_sottoscrizione',$data)){
                $model->data_sottoscrizione = $data['data_sottoscrizione'];
            }else{                
                $model->data_sottoscrizione = Carbon::now()->format(config('unidem.date_format'));
            }

            $model->data_inizio_conv = $data['data_inizio_conv'];
            $model->data_fine_conv = $data['data_fine_conv'];
            if (array_key_exists('data_stipula',$data)){
                $model->data_stipula = $data['data_stipula'];
            }else{
                $model->data_stipula = $model->data_sottoscrizione;
            }

            if (array_key_exists('num_rep',$data)&& $data['num_rep'])
                $model->num_rep = $data['num_rep'];

            if (array_key_exists('attachments',$data)){
                $this->saveAttachments($data['attachments'], $model);
            }

            if (array_key_exists('scadenze',$data) && count($data['scadenze'])>0){
                $model->scadenze()->createMany($data['scadenze']);
            }

            $transition = $data['transition'];            

            $model->workflow_apply($transition, $model->getWorkflowName());
            $model->save();

            event(new RepositoryEntityUpdated($this, $model));

        } catch (\Exception $e) {
             DB::rollback();
             throw $e;
        }

        DB::commit();
        return $this->findMinimalById($model->id);    
    }
    
    public function updateBolloRepertoriazioneStep($data){
        DB::beginTransaction();    
        try {
            $model = $this->model->findOrFail($data['convenzione_id']);         
            
            if (array_key_exists('bollo_virtuale',$data)){
                $model->bollo_virtuale = $data['bollo_virtuale'];
                if ($model->bollo_virtuale){
                    if (array_key_exists('bollo_atti', $data))
                    {               
                        $model->bolli()->delete();
                        $model->bolli()->createMany([$data['bollo_atti'], $data['bollo_allegati']]);               
                    }                    
                    
                }
                
            }

            if (array_key_exists('num_rep',$data['attachments'][0])){
                $model->num_rep = $data['attachments'][0]['num_rep'];
            }                

            if (array_key_exists('attachments',$data)){
                $this->saveAttachments($data['attachments'], $model);
            }else{
                throw new Exception("Nessun file in allegato", 1);                
            }    

            $model->workflow_apply(Convenzione::REPERTORIO, $model->getWorkflowName());
            $model->save();

            event(new RepositoryEntityUpdated($this, $model));

        } catch (\Exception $e) {
             DB::rollback();
             throw $e;
        }

        DB::commit();
        return $this->findMinimalById($model->id);    
    }


    public function applyTransition($transition, $model){
        DB::beginTransaction();     
        try {
            
            $model->workflow_apply($transition, $model->getWorkflowName());
            $model->save();           

            event(new RepositoryEntityUpdated($this, $model));
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
       }

       DB::commit();
       return $model;
    }

    /**
     * saveAttachments
     *
     * @param  mixed $data lista di allegati
     * @param  mixed $model istanza del modello a cui associare i file
     * @param  mixed $emptyPermission
     * @return void
     */
    public function saveAttachments($data, $model, $emptyPermission = false){
        $attachment = null;
        foreach ($data as $valore){ 
            $saved = false;

            $valore['model_type'] = get_class($model);        
            $attachment = new Attachment($valore);   
          
            $attachment->model()->associate($model);
            if (array_key_exists('filevalue',$valore) && $attachment->loadStream($valore['filevalue']) != null ){     
                $attachment->setDescription();           
                $model->attachments()->save($attachment);
                $saved = true;
            }else{                
                if ($attachment->nrecord && $attachment->num_prot && $attachment->createLink($attachment->num_prot)){                    
                    $model->attachments()->save($attachment);
                    $saved = true;
                } else{
                    if ($emptyPermission && $attachment->createEmptyFile()){
                        $model->attachments()->save($attachment);
                        $saved = true;
                    }                    
                }
            }

            if (array_key_exists('id',$valore) && $saved){  
                $attach = Attachment::find($valore['id']);
                if ($attach)
                    $attach->delete();
            } 
                        
        }
        return $attachment;
    }

    public function updateRichiestaEmissione($data){
        DB::beginTransaction();     
        try {
            //leggere scadenza
            $scad = Scadenza::find($data['id']);
            $scad->data_emisrichiesta =  Carbon::now()->format(config('unidem.date_format'));            
            $scad->tipo_emissione = $data['tipo_emissione'];
            //da attivo a inemissione
            $scad->workflow_apply('richiestaemissione', $scad->getWorkflowName());        
            $scad->save();

            //task che registra la richiesta di emissione della scadenza             
            $task = new RichiestaEmissioneTask($scad);                
            $usertask = $task->save();

            $usertask->workflow_apply(UserTask::STORE_ESEGUITO,'usertask');
            $now = new DateTime();
            $usertask->description = $task->description.' Completato da '.Auth::user()->name.' in data '.$now->format('d-m-Y');
            $usertask->closing_user_id = Auth::user()->id;
            $usertask->save();

            //creare e salvare un usertask 
            $task = new EmissioneTask($scad);
            $task->owner(Auth::user()->id)
                ->setAssignments($data['assignments'])
                ->respons($data['respons_v_ie_ru_personale_id_ab'])
                ->unitaorganizzativa($data['unitaorganizzativa_uo'])
                ->data([                    
                    'description' => $data['description']                    
                ]);

            $task->save();           
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
       }

       DB::commit();
       return $scad;
    }

    public function updateInvioRichiestaPagamento($data){
        DB::beginTransaction();       
        try {
            //leggere scadenza
            $scad = Scadenza::find($data['id']);

            $scad->data_emisrichiesta =  Carbon::now()->format(config('unidem.date_format'));
            $scad->tipo_emissione = 'RICHIESTA_PAGAMENTO';          

            $scad->workflow_apply('richiestapagamento', $scad->getWorkflowName());        
            $scad->save();


             //task che registra la richiesta di emissione della scadenza             
             $task = new InvioRichiestaPagamentoTask($scad);                
             $usertask = $task->save();
 
             $usertask->workflow_apply(UserTask::STORE_ESEGUITO,'usertask');
             $now = new DateTime();
             $usertask->description = $task->description.' Completato da '.Auth::user()->name.' in data '.$now->format('d-m-Y');
             $usertask->closing_user_id = Auth::user()->id;
             $usertask->save();

            //salvare allegati ...             
            if (array_key_exists('attachments',$data)){              
                $this->saveAttachments($data['attachments'], $scad);
            }else{
                throw new Exception("Nessun file in allegato", 1);                
            }      

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
       }

       DB::commit();
       return $scad;
    }


    
    public function updateEmissione($data){
        DB::beginTransaction();
        try {
            //leggere scadenza
            $scad = Scadenza::find($data['id']);
            
            //salvare eventuale numero fattura e numero protocollo
            if ($data['attachment1']['attachmenttype_codice'] == 'FATTURA_ELETTRONICA' || $data['attachment1']['attachmenttype_codice'] == 'NOTA_DEBITO'){
                $scad->data_fattura = $data['data_fattura'];
                $scad->num_fattura = $data['num_fattura'];
            }

            $scad->workflow_apply('emissione', $scad->getWorkflowName());        
            $scad->save();

            //salvare allegati ...             
            if (array_key_exists('attachment1',$data)){              
                $this->saveAttachments(array($data['attachment1']), $scad);
            }else{
                throw new Exception("Nessun file in allegato", 1);                
            }            

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
       }

       DB::commit();
       return $scad;
    }

    public function updateModificaEmissione($data){
        DB::beginTransaction();
        try {
            //leggere scadenza
            $scad = Scadenza::find($data['id']);
            
            $attachs = $scad->attachments->whereIn('attachmenttype_codice',['FATTURA_ELETTRONICA','NOTA_DEBITO']);
            if ($attachs){                
                $attachs->each->delete();
            }

            //salvare eventuale numero fattura e numero protocollo
            if ($data['attachment1']['attachmenttype_codice'] == 'FATTURA_ELETTRONICA' || $data['attachment1']['attachmenttype_codice'] == 'NOTA_DEBITO'){
                $scad->data_fattura = $data['data_fattura'];
                $scad->num_fattura = $data['num_fattura'];
            }

            $scad->save();

            //salvare allegati ...             
            if (array_key_exists('attachment1',$data)){              
                $this->saveAttachments(array($data['attachment1']), $scad);
            }else{
                throw new Exception("Nessun file in allegato", 1);                
            }            

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
       }

       DB::commit();
       return $scad;
    }

    
    public function updatePagamento($data){
        DB::beginTransaction();  
        try {
            //leggere scadenza
            $scad = Scadenza::find($data['id']);         

            $scad->num_ordincasso = $data['num_ordincasso'];
            $scad->data_ordincasso = $data['data_ordincasso'];
            $scad->prelievo = $data['prelievo'];
            $scad->note = $data['note'];
            
            $scad->workflow_apply('registrazionepagamento', $scad->getWorkflowName());        
            $scad->save();

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();
        return $scad;

    }


}