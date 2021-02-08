<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConvenzioneWithAziende extends Convenzione {
    public $table = 'convenzioni';
    protected $with = ['aziende:id,denominazione'];

    protected $appends = ['list_azienda_denominazione'];

    public function getListAziendaDenominazioneAttribute(){
        if ($this->aziende){
            return $this->listAziendaDenominazione();
        }
        return null;
    }

}