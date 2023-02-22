<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bollo extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bolli';

    protected $fillable = [
        'id',
        'convenzioni_id',
        'tipobolli_codice',
        'num_bolli',
        'num_righe',
        'num_pagine'
    ];


    //In your example, if A has a b_id column, then A belongsTo B.
    //If B has an a_id column, then A hasOne or hasMany B depending on how many B should have.
    public function tipobollo()
    {
        return $this->belongsTo('App\TipoBollo','tipobolli_codice','codice');
    }

    public function convenzione()
    {
        return $this->belongsTo('App\Convenzione','convenzioni_id','id');
    }

    public function totale()
    {
        return $this->tipobollo()->first()->importo * $this->num_bolli;
    }   
}
