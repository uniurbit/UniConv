<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConvenzioneWithAziende extends Convenzione {
    public $table = 'convenzioni';
    protected $with = ['aziende:id,denominazione','convenzione:id,descrizione_titolo,dipartimemto_cd_dip'];

    protected $appends = ['list_azienda_denominazione','descrizione'];

    public function getListAziendaDenominazioneAttribute(){
        if ($this->aziende){
            return $this->listAziendaDenominazione();
        }
        return null;
    }

    public function getDescrizioneAttribute(){
        return "Convenzione n. ".$this->id." ".$this->descrizione_titolo." Azienda: ".$this->list_azienda_denominazione;
        //Azienda: {{ task?.modelwith?.list_azienda_denominazione }}
    }
    
    public function convenzione()
    {
        return $this->belongsTo('App\Convenzione', 'id');
    }

}