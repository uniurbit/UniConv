<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScadenzaWithConvenzione extends Scadenza {
    public $table = 'scadenze';
    protected $with = ['aziende','convenzione:id,descrizione_titolo,dipartimemto_cd_dip'];

    protected $appends = ['list_azienda_denominazione','descrizione'];

    public function convenzione()
    {
        return $this->belongsTo(ConvenzioneWithAziende::class,'convenzione_id','id');
    }

    protected function setPrimaryKey($key)
    {
        $this->primaryKey = $key;
    }

    public function aziende()
    {
        $relation = $this->belongsToMany('App\AziendaLoc','convenzione_azienda','convenzione_id','azienda_id','convenzione_id','id');        
        return $relation; 
    }
   

    public function getListAziendaDenominazioneAttribute(){
        if ($this->aziende){
            return $this->aziende->implode('denominazione', ', ');     
        }
        return null;
    }

    
    public function getDescrizioneAttribute(){
        if ($this->convenzione){
            return "Scadenza n. ".$this->id." (".$this->convenzione->descrizione.")";                
        }
        return "Scadenza n. ".$this->id;
    }
}