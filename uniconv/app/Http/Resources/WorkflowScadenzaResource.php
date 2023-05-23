<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowScadenzaResource extends JsonResource
{
    
    private $names = array(        
        "richiestaemissione" => "Richiesta emissione",
        "inemissione" => "In emissione documento di pagamento",
        "emissione" => "Emettere documento di pagamento",
        "attivo" => "Attiva",
        "emesso" => "Emesso documento di pagamento",        
        "inpagamento" => "In attesa del pagamento della ditta",
        "registrazionepagamento" => "In attesa del pagamento della ditta",
        "registrazionerendiconto" => "Rendicontata",
        "pagato" => "Rendicontata",      
        "richiestapagamento" => "Richiesta pagamento", 
        "delete" => "Cancellazione"      
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

