<?php

namespace App\Http\Controllers\Api\V1;

use App\Scadenza;
use App\Convenzione;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ScadenzaResource;
use App\Http\Resources\WorkflowUserTaskResource;
use App\Http\Resources\WorkflowScadenzaResource;
use App\Exports\ScadenzeExport;
use Auth;
class ScadenzaController extends Controller
{

    public function index()
    {
        return Scadenza::all();
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

        $entity = Scadenza::with(['usertasks','attachments','convenzione:id,descrizione_titolo','convenzione.attachments:id,model_id,model_type,attachmenttype_codice','convenzione.aziende:id,denominazione'])->find($id);
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
        $entity->delete();

        return $entity;
    }
    
    public function queryparameter(Request $request){
        
        $app = $request->json();
        $parameters = $request->json()->all();
        $parameters['includes'] = 'aziende,convenzione';
        $parameters['columns'] = 'id,data_tranche,dovuto_tranche,state,aziende.id,aziende.denominazione,convenzione_id,convenzione.id,convenzione.descrizione_titolo,convenzione.unitaorganizzativa_uo,convenzione.dipartimemto_cd_dip';

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
            }else{
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
}