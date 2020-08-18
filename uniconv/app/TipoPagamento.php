<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoPagamento extends Model
{
    public $table = 'tipopagamenti';

    public $timestamps = false;

    protected $fillable = ['id', 'codice', 'descrizione'];
    
}
