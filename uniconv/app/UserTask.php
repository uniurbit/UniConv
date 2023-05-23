<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use ZeroDaHero\LaravelWorkflow\Traits\WorkflowTrait;
use Workflow;
use Symfony\Component\Workflow\Transition;
use App\ConvenzioneWithAziende;
use App\ScadenzaWithConvenzione;
use Illuminate\Database\Eloquent\Relations\Relation;
class UserTask extends Model
{

    //STATI
    const APERTO = 'aperto';
    const INLAVORAZIONE = 'inlavorazione';
    const COMPLETATO = 'completato';
    const ANNULLATO = 'annullato';

    //
    const STORE_ESEGUITO = 'store_eseguito';
    const STORE_CONERRORI = 'store_conerrori';

    use WorkflowTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usertasks';

    protected $fillable = ['workflow_place', 'workflow_transition', 'subject', 'description', 'state','unitaorganizzativa_uo', 
        'respons_v_ie_ru_personale_id_ab', 'owner_user_id', 'closing_user_id', 'data'];
  
    protected $casts = [
        'data' => 'array',      
        'created_at' => 'datetime:d-m-Y H:m',
        'updated_at' => 'datetime:d-m-Y H:m',
    ];    

    protected $with = ['closingUser:id,name'];

    protected $appends = ['readable_created_at'];

    public function model()
    {
        Relation::morphMap([
            'App\Convenzione' => 'App\Convenzione',
            'App\Scadenza' => 'App\Scadenza',
        ]);

        return $this->morphTo();
    }

    public function modelwith()
    {
        Relation::morphMap([
            'App\Convenzione' => 'App\ConvenzioneWithAziende',
            'App\Scadenza' => 'App\ScadenzaWithConvenzione',
        ]);

        return $this->morphTo(null,'model_type','model_id');
    }

    public function checkAndChangeState(){

        if ($this->model()->first()->current_place == $this->state){
            if($this->workflow_can('store_eseguito','usertask')){
                $this->workflow_apply('store_eseguito','usertask');
                $this->save();
                return true;
            }
        }

        return false;
    }
 
    public function workflow_transitions_self()
    {
        $list = Workflow::get($this)->getEnabledTransitions($this);
        array_unshift($list, new Transition("self_transition",[$this->state],[$this->state]));
        return $list;
    }

    public function tasktype()
    { 
        return $this->belongsTo(TaskType::class,'tasktypes_id','id');
    }

    public function scopeWithTaskType($query)
    {
        return $query->with(['tasktype']);
    }

    public function assignments() {

        return $this->morphMany(ModelHasAssignments::class, 'model');
    }

    //restiutisce il responsabile ufficio leggendo dalla tabella assegnamenti
    public function respons(){

        return $this->assignments()->respons();
    }

    public function user()
    {
        return $this->belongsTo(User::class,'owner_user_id','id');
    }
    
    public function closingUser()
    {
        return $this->belongsTo(User::class,'closing_user_id','id');
    }

    public function unitaOrganizzativa()
    {
        //va verso oracle
        return $this->belongsTo(UnitaOrganizzativa::class, 'unitaorganizzativa_uo',  'uo');         
    }

    public function getReadableCreatedAtAttribute()
    {
        return $this->created_at; 
    }
}
