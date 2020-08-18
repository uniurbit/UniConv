<?php

namespace App\Http\Resources;

use App\Convenzione;
use Illuminate\Http\Resources\Json\Resource;

class WorkflowConvenzione extends Resource
{
        
  
    private $names = array(
        'start' => 'Inizio',
        'store_proposta' => 'Proposta',
        'store_to_inapprovazione' => "Da approvare",
        'store_validazione' => "Approvato",
        'firma_da_controparte1' => "Firmato controparte",      
        'firma_da_direttore2' => 'Firmato direttore',
        'firma_da_direttore1' => 'Firmato direttore',
        'firma_da_controparte2' => 'Firmato controparte',            
        'repertorio' => 'Repertoriato',
        'cancella_sottoscrizione_uniurb' => 'Annulla sottoscrizione',
        'cancella_sottoscrizione_contr' => 'Annulla sottoscrizione',


        'repertoriato' => 'Repertoriato',
        'proposta' => 'Proposta',
        'inapprovazione' => 'Da approvare',
        'approvato' => 'Approvato',
        'da_firmare_direttore' => 'Firmato controparte',
        'da_firmare_controparte2' => 'Firmato direttore',
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

