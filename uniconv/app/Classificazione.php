<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classificazione extends Model
{
    protected $table = 'classificazioni';

    use SoftDeletes;

    
    public $fillable = ['codice','descrizione'];
    
     /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

}
