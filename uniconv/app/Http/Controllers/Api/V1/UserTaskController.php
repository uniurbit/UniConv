<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Attachment;
use App\Http\Controllers\Controller;
use Validator;
use App\Convenzione;
use App\Scadenza;
use App\UserTask;
use App\User;
use App\Personale;
use Auth;
use App\Http\Resources\WorkflowUserTaskResource;

class UserTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {        
        return UserTask::with(['tasktype'])->get();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function filterByUser(Request $request)
    {        
        $id = $request->id;
        //Auth::user()->id                             
        $user=User::find($id);
        $result = UserTask::with(['tasktype','user','assignments.personale','modelwith'])
            ->whereHas('assignments', function ($query) use($user) {
                $query->where('v_ie_ru_personale_id_ab', '=', $user->v_ie_ru_personale_id_ab);
            })
            ->where('state',UserTask::APERTO)
            ->orWhere('state',UserTask::INLAVORAZIONE)
            ->orderBy('id','desc')
            ->paginate();
        return $result;                        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function filterByUfficio(Request $request)
    {        
        //Auth::user()->id      
        $id = $request->id;               
        $user=User::find($id);        
        $uos = $user->codiciUnitaorganizzative();
        return UserTask::with(['tasktype','user','closingUser','assignments.personale', 'modelwith'])
            ->whereIn('unitaorganizzativa_uo',$uos)
            ->orderBy('updated_at','desc')->paginate();           
    }


    public function filterByConvenzione($convId)
    {        
        return UserTask::with(['tasktype'])->where('model_id', $convId)->get();
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //tolgo model:descrizione_titolo perchè è una query polimorfica 
        if (!Auth::user()->hasPermissionTo('view usertasks')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }        

        $task = UserTask::with(['tasktype', 'assignments' => function($query){
            return $query->whereNotIn('cd_tipo_posizorg',['RESP_UFF', 'COOR_PRO_D'])->orWhereNull('cd_tipo_posizorg');
        }])->with(['model'])->find($id);

        if ($task){
            WorkflowUserTaskResource::withoutWrapping();
            $task['transitions'] = WorkflowUserTaskResource::collection(collect($task->workflow_transitions_self()));                     
        }

        return $task;
    }

    private function filtriPermessiRicercaUserTask($parameters)
    {
        //se l'utente non è super-admin 
        if (!Auth::user()->hasRole('super-admin')){
            //aggiungere filtro per unitaorganizzativa_uo
            //voglio tutte gli usertask che afferiscono al plesso o ai dipartimenti ... 

            $uo = Auth::user()->unitaorganizzativa();
            if ($uo->isDipartimento()){
                //ad un afferente al dipartimento filtro per dipartimento                
                array_push($parameters['rules'],[
                    "operator" => "=",
                    "field" => "unitaorganizzativa_uo",                
                    "value" => $uo->uo
                ]);
            }else if ($uo->isPlesso()){
                //filtro per unitaorganizzativa dell'utente di inserimento (plesso)
                $uos = $uo->dipartimenti_uo();
                array_push($uos,$uo->uo);
                array_push($parameters['rules'],[
                    "operator" => "In",
                    "field" => "unitaorganizzativa_uo",                
                    "value" => $uos
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

        if (!Auth::user()->hasPermissionTo('view usertasks')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }       

      
        $app = $request->json();
        $parameters = $request->json()->all();
        $parameters['includes'] = 'tasktype,modelwith';

        //usertask hanno una afferenza organizzativa 
        //poi sono anche collegati ad una convenzione o scadenza ...         
        //se sono un super-admin vedo tutto 
        
        $parameters = $this->filtriPermessiRicercaUserTask($parameters);

        $findparam =new \App\FindParameter($parameters);   

        $queryBuilder = new QueryBuilder(new UserTask, $request, $findparam);
                
        return $queryBuilder->build()->paginate();       

    }

    public function create(){

        $task = new UserTask();
        if ($task){
            WorkflowUserTaskResource::withoutWrapping();
            $task['transitions'] = WorkflowUserTaskResource::collection(collect($task->workflow_transitions_self()));                     
        }     
        $task->model_type = Convenzione::class;
        $task->model =[
            'id' => null,
            'descrizione_titolo' => null,
        ];
        return $task;
    }


    public function nextPossibleActions($id){
        $task = UserTask::find($id);
        if ($task){
            WorkflowUserTaskResource::withoutWrapping();
            return WorkflowUserTaskResource::collection(collect($task->workflow_transitions_self()));                     
        }                
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
        //si possono modificare solo gli assegnatari, il testo e la descrizione
        if (!Auth::user()->hasPermissionTo('update usertasks')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }       

        //se lo stato della convenzione associata coincide con quello memorizzato 
        //il task si può mettere in stato completato            
        $task = UserTask::find($id);
        if ($request->transition !== 'self_transition'){
            if (!Auth::user()->hasPermissionTo($request->transition.' usertasks')) {
                abort(403, trans('global.utente_non_autorizzato'));
            }  
            $task->workflow_apply($request->transition,'usertask');      
        }
        $task->update($request->except('state'));
        
        $task->save();
        //where('cd_tipo_posizorg','RESP_UFF')
        $task->assignments()->delete();
        //assignments contiene il responsabile di ufficio
        //$task->assignments()->create(['v_ie_ru_personale_id_ab' => $task->respons_v_ie_ru_personale_id_ab, 'cd_tipo_posizorg' => 'RESP_UFF']);
        $task->assignments()->createMany($request->assignments);
        
        return $this->show($id);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {        
        if (!Auth::user()->hasPermissionTo('create usertasks')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }       
        
        $task = new UserTask();
 
        $this->validate($request, [
            'unitaorganizzativa_uo'=>'required',
            'description' =>'required',
            'respons_v_ie_ru_personale_id_ab' => 'required',
            ]
        );

        $task->fill($request->all());    
        $task->fill([        
            'owner_user_id' => Auth::user()->id,                     
            'state' => 'aperto',
        ]);            

        $entity = null;
        if ($request->model_type == 'App\Scadenza'){           
            $entity = Scadenza::find($request->model_id);
            $task->workflow_place = $entity->state;   
        }else{
            $entity = Convenzione::find($request->model_id);      
            $task->workflow_place = $entity->current_place;      
        }
        //dato che è un api serve per il controllo
        
        $task->model()->associate($entity);            
        $task->save();   
        
        //$task->assignments()->create(['v_ie_ru_personale_id_ab' => $task->respons_v_ie_ru_personale_id_ab, 'cd_tipo_posizorg' => 'RESP_UFF']);        
        $task->assignments()->createMany($request->assignments);        

        return $this->show($task->id);
    }

}
