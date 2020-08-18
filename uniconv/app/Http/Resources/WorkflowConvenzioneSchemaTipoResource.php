<?php

namespace App\Http\Resources;

use App\Convenzione;
use Illuminate\Http\Resources\Json\Resource;


class WorkflowConvenzioneSchemaTipoResource extends Resource
{
        
    private $names = array(
        'store_proposta' => "Proposta",
        'store_to_approvato' => "Approvato",
        'firma_da_direttore1' => "Firmato dal direttore",        
        'proposta' => 'Proposta',
        'approvato' => 'Approvato',
        'firmato' => 'Firmato',
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

