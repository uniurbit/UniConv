<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Attachment;
use App\Http\Controllers\Controller;
use Validator;
use App\Convenzione;
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
        $pers = Personale::find($user->v_ie_ru_personale_id_ab);       
        return UserTask::with(['tasktype','user','closingUser','assignments.personale', 'modelwith'])
            ->where('unitaorganizzativa_uo',$pers->aff_org)
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
        }])->with(['model','unitaOrganizzativa:descr'])->find($id);

        if ($task){
            WorkflowUserTaskResource::withoutWrapping();
            $task['transitions'] = WorkflowUserTaskResource::collection(collect($task->workflow_transitions_self()));                     
        }

        return $task;
    }


    public function query(Request $request){       

        if (!Auth::user()->hasPermissionTo('view usertasks')) {
            abort(403, trans('global.utente_non_autorizzato'));
        }       

        $app = $request->json();
        $parameters = $request->json()->all();
        $parameters['includes'] = 'tasktype';
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

        //dato che è un api serve per il controllo
        $conv = Convenzione::find($request->model_id);
        $task->model()->associate($conv);            
        $task->save();   
        
        //$task->assignments()->create(['v_ie_ru_personale_id_ab' => $task->respons_v_ie_ru_personale_id_ab, 'cd_tipo_posizorg' => 'RESP_UFF']);        
        $task->assignments()->createMany($request->assignments);        

        return $this->show($task->id);
    }

}
