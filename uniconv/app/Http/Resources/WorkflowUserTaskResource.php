<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowUserTaskResource extends JsonResource
{

    
    private $names = array(
        "store_aperto" => "Aperto",
        "store_presaincarico" => "In lavorazione",
        "store_eseguito" => "Completato",
        "store_conerrori" => "Annullato",
        "aperto" => "Aperto",
        "inlavorazione" => "In lavorazione",
        "annullato"  => "Annullato",
        "completato"  => "Completato",
    );

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {                    
        return [
            'label'   => ($this->resource->getName() == "self_transition") ?  $this->names[$this->resource->getFroms()[0]] : $this->names[$this->resource->getName()],
            'value'   => $this->resource->getName(),      
            'transitions'=> $this->resource,                  
        ];
    }
}

