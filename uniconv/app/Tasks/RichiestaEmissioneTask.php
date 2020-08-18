<?php

namespace App\Tasks;

use App\Convenzione;
use App\PersonaleResponsOrg;
use App\User;
use App\UserTask;
use App\Scadenza;
use Auth;

class RichiestaEmissioneTask extends BaseTask
{
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($scad)
    {
        $this->model = $scad;
        $this->workflow_place =  Scadenza::INEMISSIONE; 
        $this->workflow_transition = Scadenza::EMISSIONE;
        $this->subject = 'Emissione';                
        $this->state = 'aperto';                              
    }     
   
    public function toUserTask(){
        $usertask = new  UserTask();
        $usertask->model()->associate($this->model);
        $usertask->fill($this->toArray());        
        return $usertask;
    }

    public function save()
    {
        $usertask = $this->toUserTask();
        $usertask->save();        
        $usertask->assignments()->createMany($this->assignments);
    }
}