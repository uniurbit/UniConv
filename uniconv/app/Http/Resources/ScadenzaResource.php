<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ScadenzaResource extends Resource
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
            'convenzione'   => [
                'id' => null,
                'descrizione_titolo' => null,
            ]
        ];
    }

}
