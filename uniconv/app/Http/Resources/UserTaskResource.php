<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class UserTaskResource extends Resource
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
            //'descr'   => $this->onlyFirstUpper((string)$this->nome).' '.$this->onlyFirstUpper((string)$this->cognome),
            'id' => $this->id,                        
            'description' => (string)$this->description,
        ];
    }

    private function onlyFirstUpper($value){
        return ucwords(strtolower($value));
    }
}
