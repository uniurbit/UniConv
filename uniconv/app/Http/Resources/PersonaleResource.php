<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonaleResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'descr'   => $this->onlyFirstUpper((string)$this->nome).' '.$this->onlyFirstUpper((string)$this->cognome),
            'id'   => (string)$this->id_ab,                        
            'cd_tipo_posizorg' => (string)$this->cd_tipo_posizorg,
        ];
    }

    private function onlyFirstUpper($value){
        return ucwords(strtolower($value));
    }
}
