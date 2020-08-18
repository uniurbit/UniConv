<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ConvenzioneAzienda extends Pivot
{
    
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'convenzione_azienda';

    public function convenzione() {
        return $this->belongsTo('App\Convenzione');
    }
    
    public function azienda() {
        return $this->belongsTo('App\Azienda');
    }
}
