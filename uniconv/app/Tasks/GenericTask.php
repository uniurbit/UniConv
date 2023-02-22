<?php

namespace App\Tasks;

use App\Convenzione;
use App\PersonaleResponsOrg;
use App\User;
use App\UserTask;
use Auth;

class GenericTask extends BaseTask
{
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($conv)
    {
        $this->model = $conv;
       
        //aggiunge il responsabile di struttura tra gli assegnatari del task
        $this->assignments();
    }     

    public function assignments(){
        //cercare utente nella tabella ... v_ie_ru_pers_respons_org    
        $user = User::find($this->model->user_id)->findPersonaleRespons();  
        //il referente della convenzione è l'utente che ha compilato 
        if ($this->model->convenzione_from == 'amm'){
            //per le convenzioni amministrative
            $this->unitaorganizzativa_uo = $this->model->unitaorganizzativa_uo;
        } else {                          
            $this->unitaorganizzativa_uo = $user->cd_csa;
        }
        $this->respons_v_ie_ru_personale_id_ab = $user->responsabileUfficio(); //$user->id_ab_resp; 
        array_push($this->assignments, ['v_ie_ru_personale_id_ab' => $user->id_ab, 'cd_tipo_posizorg' => $user->cd_tipo_posizorg]);  
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