<?php

namespace App\Tasks;

use Auth;

class BaseTask 
{  
    

    /**
     * The task's associate model.
     *
     * @var array
     */
    public $assignments = [];    
 
    /**
     * Set the assignments of the task.
     *
     * @param  string  $assignments
     * @return $this
     */
    public function setAssignments($assignments)
    {
        $this->assignments = $assignments;

        return $this;
    }


   /**
     * The task's associate model.
     *
     * @var string
     */
    public $model;    

    /**
     * Set the model associate of the task.
     *
     * @param  string  $model
     * @return $this
     */
    public function model($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * The task's state.
     *
     * @var string
     */
    public $state = 'aperto';    

    /**
     * Set the subject of the task.
     *
     * @param  string  $subject
     * @return $this
     */
    public function state($state)
    {
        $this->state = $state;

        return $this;
    }

     /**
     * The task's subject.
     *
     * @var string
     */
    public $subject;    

    /**
     * Set the subject of the task.
     *
     * @param  string  $subject
     * @return $this
     */
    public function subject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * The task's description.
     *
     * @var string
     */
    public $description;    

    /**
     * Set the description of the task.
     *
     * @param  string  $description
     * @return $this
     */
    public function description($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * The task's workflow_transition.
     *
     * @var string
     */
    public $workflow_transition;    

    /**
     * Set the workflow_transition of the task.
     *
     * @param  string  $workflow_transition
     * @return $this
     */
    public function workflow_transition($workflow_transition)
    {
        $this->workflow_transition = $workflow_transition;

        return $this;
    }


    /**
     * The task's workflow_place.
     *
     * @var string
     */
    public $workflow_place;    

    /**
     * Set the workflow_place of the task.
     *
     * @param  string  $workflow_place
     * @return $this
     */
    public function workflow_place($workflow_place)
    {
        $this->workflow_place = $workflow_place;

        return $this;
    }

     /**
     * The task's owner_user_id.
     *
     * @var string
     */
    public $owner_user_id;    

    /**
     * Set the owner_user_id of the task.
     *
     * @param  string  $owner_user_id
     * @return $this
     */
    public function owner($userid)
    {
        $this->owner_user_id = $userid;

        return $this;
    }

    /**
     * The task's unitaorganizzativa_uo.
     *
     * @var string
     */
    public $unitaorganizzativa_uo;    

    /**
     * Set the unitaorganizzativa_uo of the task.
     *
     * @param  string  $unitaorganizzativa_uo
     * @return $this
     */
    public function unitaorganizzativa($unitaorganizzativa_uo)
    {
        $this->unitaorganizzativa_uo = $unitaorganizzativa_uo;

        return $this;
    }


     /**
     * The task's unitaorganizzativa_uo.
     *
     * @var string
     */
    public $respons_v_ie_ru_personale_id_ab;    

    /**
     * Set the unitaorganizzativa_uo of the task.
     *
     * @param  string  $respons_v_ie_ru_personale_id_ab
     * @return $this
     */
    public function respons($respons_v_ie_ru_personale_id_ab)
    {
        $this->respons_v_ie_ru_personale_id_ab = $respons_v_ie_ru_personale_id_ab;

        return $this;
    }
    
    public $data;

     /**
     * Set the array of data associate of the task.
     *
     * @param  string  $data
     * @return $this
     */
    public function data($arrdata)
    {
        $this->data = $arrdata;

        return $this;
    }
    /**
     * Get the array representation of the task.
     *     
     * @return array
     */
    public function toArray()
    {
        return [
            'state' => $this->state,
            'subject' => $this->subject,  
            'description' => $this->description,  
            'workflow_transition' => $this->workflow_transition,        
            'workflow_place' => $this->workflow_place,
            'owner_user_id' => $this->owner_user_id,
            'model_id' => $this->model->id,
            'model_type' => get_class($this->model),
            'respons_v_ie_ru_personale_id_ab' => $this->respons_v_ie_ru_personale_id_ab,
            'unitaorganizzativa_uo' => $this->unitaorganizzativa_uo,
            'assignments' => $this->assignments,
            'data' => $this->data,
        ];
    }
}