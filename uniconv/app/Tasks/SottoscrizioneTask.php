<?php

namespace App\Tasks;

use App\Convenzione;
use App\PersonaleResponsOrg;
use App\User;
use App\UserTask;
use Auth;

class SottoscrizioneTask extends GenericTask
{
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($conv)
    {
        $this->model = $conv;
        $this->subject = "Sottoscrizione";
        $this->workflow_place = Convenzione::APPROVATO;             
        $this->assignments();
    }     
   
}