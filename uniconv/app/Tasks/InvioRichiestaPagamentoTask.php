<?php

namespace App\Tasks;

use App\Convenzione;
use App\PersonaleResponsOrg;
use App\User;
use App\UserTask;
use App\Scadenza;
use Auth;

class InvioRichiestaPagamentoTask extends RichiestaEmissioneTask
{
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($scad)
    { 
        $this->model = $scad;
        $this->workflow_place =  Scadenza::ATTIVO; 
        $this->workflow_transition = 'richiestapagamento';
        $this->subject = 'Inviata richiesta di pagamento'; 
        //chi fa la richiesta di emissione     
        $this->owner_user_id = Auth::user()->id;
        $this->assignments($this->owner_user_id);
    }     
       
}