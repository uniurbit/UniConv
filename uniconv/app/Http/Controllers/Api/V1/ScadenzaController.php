<?php

namespace App\Http\Controllers\Api\V1;

use App\Scadenza;
use App\Convenzione;
use App\UserTask;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ScadenzaResource;
use App\Http\Resources\WorkflowUserTaskResource;
use App\Http\Resources\WorkflowScadenzaResource;
use App\Exports\ScadenzeExport;
use DateTime;
use Auth;
class ScadenzaController extends Controller
{

    public function index()
    {
        return Scadenza::all();
    }

    private function autorizzazioneShowScadenza($scad)
    {
        //se l'utente non ha il peremesso 'search all scadenze' va filtrato 
        if (!Auth::user()->hasPermissionTo('search all scadenze')){
            //aggiungere controllo per unitaorganizzativa_uo dell'utente che esegue la richiesta ...
            $uo = Auth::user()->unitaorganizzativa();
            if ($uo->isDipartimento()){    
                //caso docenti che va ulteriormente filtrato ... 
                //ad un afferente al dipartimento filtro per dipartimento       
                $dip = $uo->dipartimento()->first();    
                if (!in_array($scad->convenzione->dipartimemto_cd_dip,[$dip->cd_dip])){
                    abort(403, trans('global.utente_non_autorizzato')); 
                }   
            }else if ($uo->isPlesso()){                 
                if (!in_array($scad->convenzione->dipartimemto_cd_dip, $uo->dipartimenti_cd_dip())){
                    abort(403, trans('global.utente_non_autorizzato')); 
                }  
            }else if (Auth::user()->hasRole('op_contabilita')){                
                //$aff_org = Auth::user()->personaleRelation()->first()->aff_org;                
                if (!in_array($uo->uo, $scad->usertasks()->pluck('unitaorganizzativa_uo')->toArray())){
                    abort(403, trans('global.utente_non_autorizzato')); 
                } 
            }else if (Auth::user()->hasRole('op_approvazione')){
                //op_approvazione
                //$aff_org = Auth::user()->personaleRelation()->first()->aff_org;                
                if (!in_array($uo->uo, $scad->convenzione->usertasks()->pluck('unitaorganizzativa_uo')->toArray())){
                    abort(403, trans('global.utente_non_autorizzato')); 
                } 
            }else{
                //filtro per unitaorganizzativa dell'utente di inserimento (ufficio)
                //se l'utente è responsabile di più uffici, filtro per unitaorganizzativa di ogni ufficio
                $uos = Auth::user()->codiciUnitaorganizzative();
                if (!in_array($scad->convenzione->unitaorganizzativa_uo, $uos)){
                    abort(403, trans('global.utente_non_autorizzato')); 
                }  
            }          
        }

        return true;
    }
 
    public function show($id)
    {      
        if ($id == 'new'){
            ScadenzaResource::withoutWrapping();
            return new ScadenzaResource(null);
        }  
        
        if (!Auth::user()->hasPermissionTo('view scadenze')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }
        
       
        $entity = Scadenza::with(['usertasks','usertasks.assignments.user','attachments','convenzione:id,descrizione_titolo,dipartimemto_cd_dip','convenzione.attachments:id,model_id,model_type,attachmenttype_codice','convenzione.aziende:id,denominazione'])->find($id);
       
        //esistono ulterirori logiche di permesso di visualizzazione della singola scadenza 
        
        //op_contabilita --> solo se esiste un task assegnato all'ufficio di afferenza
        //viewer --> se è della stessa unità organizzativa 
        //op_docente --> se è della stessa unità organizzativa 
        $this->autorizzazioneShowScadenza($entity);

        if ($entity){
            WorkflowScadenzaResource::withoutWrapping();
            $entity['transitions'] = WorkflowScadenzaResource::collection(collect($entity->workflow_transitions_self()));                     
        }
        return $entity;
    }

    public function store(Request $request)
    {

        if (!Auth::user()->hasPermissionTo('create scadenze')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $this->validate($request, [
            'convenzione' => 'required',
            'data_tranche'=>'required',
            'dovuto_tranche'=>'required',            
            ]
        );

        //convenzione cancellata
        Convenzione::findOrFail($request->convenzione['id']); 

        $entity = new Scadenza($request->all());
        $entity->convenzione()->associate(new Convenzione($request->convenzione));

        $entity->save();
        //$entity = Scadenza::create($request->all());

        return response()->json($entity, 201);
    }

    public function update(Request $request, $id)
    {

        if (!Auth::user()->hasPermissionTo('update scadenze')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $this->validate($request, [
            'id'=>'required',            
            ]
        );
        
        $entity = Scadenza::findOrFail($id);
        $entity->update($request->all());

        return $this->show($id);
    }

    public function delete(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('delete scadenze')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }
        

        //se è utilizzato non si può cancellare
        $entity = Scadenza::findOrFail($id);        
        if (!($entity->workflow_can('delete', $entity->getWorkflowName()))) {            
            abort(500, trans('global.scad_stato_non_valido'));
        }        

        //annullare i task associati ... alla scadenza
        $tasks = $entity->usertasks()->get();
        $this->annullaTasks($tasks);
                
        $entity->delete();

        return $entity;
    }
    
    public function queryparameter(Request $request){
        
        $app = $request->json();
        $parameters = $request->json()->all();
        $parameters['includes'] = 'aziende,convenzione';
        $parameters['columns'] = 'id,data_tranche,dovuto_tranche,state,aziende.id,aziende.denominazione,convenzione_id,convenzione.id,convenzione.descrizione_titolo,convenzione.convenzione_from,convenzione.unitaorganizzativa_uo,convenzione.dipartimemto_cd_dip';

        //se l'utente NON è un super-admin le query vanno filtrate per appartenenza
        if (!Auth::user()->hasPermissionTo('search all scadenze')){           
            //aggiungere filtro per unitaorganizzativa_uo
            $uo = Auth::user()->unitaorganizzativa();
            if ($uo->isDipartimento()){
                //ad un afferente al dipartimento filtro per dipartimento            
                $dip = $uo->dipartimento()->first();
                array_push($parameters['rules'],[
                    "operator" => "=",
                    "field" => "convenzione.dipartimemto_cd_dip",                
                    "value" => $dip->cd_dip
                ]);
            }else if ($uo->isPlesso()){
                //filtro per unitaorganizzativa dell'utente di inserimento (plesso)
                array_push($parameters['rules'],[
                    "operator" => "In",
                    "field" => "convenzione.dipartimemto_cd_dip",                
                    "value" => $uo->dipartimenti_cd_dip()
                ]);
            }else if (Auth::user()->hasRole('op_contabilita')){  
                //se esiste un task con riferimento alla uo di appartenza del user
                $parameters['includes'] = 'aziende,convenzione,usertasks';      
                $parameters['columns'] = 'id,data_tranche,dovuto_tranche,state,aziende.id,aziende.denominazione,convenzione_id,convenzione.id,convenzione.descrizione_titolo,convenzione.convenzione_from,convenzione.unitaorganizzativa_uo,convenzione.dipartimemto_cd_dip,usertasks.model_id,usertasks.model_type,usertasks.unitaorganizzativa_uo';
                //$aff_org = Auth::user()->personaleRelation()->first()->aff_org;                
                array_push($parameters['rules'],[
                    "operator" => "=",
                    "field" => "usertasks.unitaorganizzativa_uo",                
                    "value" => $uo->uo
                ]);
            }else {                
                //filtro per unitaorganizzativa dell'utente di inserimento (servizio o un plesso)
                array_push($parameters['rules'],[
                    "operator" => "=",
                    "field" => "convenzione.unitaorganizzativa_uo",                
                    "value" => $uo->uo
                ]);
            }                       
        }


       return new \App\FindParameter($parameters);      

    }

    public function query(Request $request){ 

        if (!Auth::user()->hasAnyPermission(['search all scadenze', 'search orgunit scadenze'])) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $findparam = $this->queryparameter($request);  
        $queryBuilder = new QueryBuilder(new Scadenza, $request, $findparam);
                
        return $queryBuilder->build()->paginate();       

        //costruzione della query
    }

    public function nextPossibleActions($id){
        $entity = Scadenza::find($id);
        if ( $entity){
            WorkflowScadenzaResource::withoutWrapping();
            return WorkflowScadenzaResource::collection(collect($entity->workflow_transitions_self()));                     
        }                
    }

    public function export(Request $request){
        
        if (!Auth::user()->hasAnyPermission(['search all scadenze', 'search orgunit scadenze'])) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        //prendi i parametri 
        $findparam = $this->queryparameter($request);          
        return (new ScadenzeExport($request,$findparam))->download('scadenze.csv', \Maatwebsite\Excel\Excel::CSV,  [
            'Content-Type' => 'text/csv',
        ]);        
    }

    public function exportxls(Request $request){
        
        if (!Auth::user()->hasAnyPermission(['search all scadenze', 'search orgunit scadenze'])) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        //prendi i parametri 
        $findparam = $this->queryparameter($request);          
        return (new ScadenzeExport($request,$findparam))->download('scadenze.xlsx');
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