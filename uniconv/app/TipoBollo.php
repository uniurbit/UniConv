<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoBollo extends Model
{
        
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tipobolli';

    protected $fillable = [
        'id',
        'codice',
        'descrizione',
        'importo'
    ];

}
