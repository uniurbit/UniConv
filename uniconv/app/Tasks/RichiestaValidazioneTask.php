<?php

namespace App\Tasks;

use App\Convenzione;
use App\PersonaleResponsOrg;
use App\User;
use App\UserTask;
use App\Scadenza;
use Auth;

class RichiestaValidazioneTask extends BaseTask
{
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($conv)
    {
        $this->model = $conv;
        $this->workflow_place =  Convenzione::INAPPROVAZIONE;
        $this->workflow_transition = Convenzione::STORE_VALIDAZIONE;  
        $this->subject = 'Approvazione';                
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

    //funzione utilizzata nel caso di convenzioni amministrative 
    //dove il task è associato all'utente che compila la convenzione
    public function assignments($user_id){
        //cercare utente nella tabella ... v_ie_ru_pers_respons_org    
        $user = User::find($user_id)->personaleRespons()->first();                            
        $this->unitaorganizzativa_uo = $user->cd_csa;
        $this->respons_v_ie_ru_personale_id_ab = $user->id_ab_resp;
        array_push($this->assignments, ['v_ie_ru_personale_id_ab' => $user->id_ab, 'cd_tipo_posizorg' => $user->cd_tipo_posizorg]);
    }
}