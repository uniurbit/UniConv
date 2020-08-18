<?php

namespace App\Tasks;

use App\Convenzione;
use App\PersonaleResponsOrg;
use App\User;
use App\UserTask;
use App\Scadenza;
use Auth;

class InPagamentoTask extends BaseTask
{
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($scad)
    {
        $this->model = $scad;
        $this->workflow_place =  Scadenza::INPAGAMENTO; 
        $this->workflow_transition = Scadenza::REGISTRAZIONEPAGAMENTO;
        $this->subject = 'In pagamento';      
        $this->owner_user_id = $scad->convenzione->user_id;
        $this->assignments($this->owner_user_id);
    }     
   
    public function assignments($user_id){
        //cercare utente nella tabella ... v_ie_ru_pers_respons_org    
        $user = User::find($user_id)->personaleRespons()->first();                            
        $this->unitaorganizzativa_uo = $user->cd_csa;
        $this->respons_v_ie_ru_personale_id_ab = $user->id_ab_resp;
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