<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class AziendaLoc extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'aziende';
    public $primaryKey = 'id';
   
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'nome',
        'cognome',
        'denominazione',
        'contatto_email',
        'pec_email',
        'azienda_id_esterno',
        'cod_fisc',
        'indirizzo1',
        'stato',
        'comune',
        'cap',
        'phone_number',
        'cell_phone'
    ];

    public function azienda()
    {
        return $this->hasOne('App\Azienda','id_esterno','azienda_id_esterno');
    }

      
    /**
     * indirizzoToString
     *
     * @return string esempio: costruzione stringa Via Manzoni, 2 - 40033 Casalecchio di Reno (BO) 
     */
    public function indirizzoToString(){
        $ind = '';
        if ($this->indirizzo1){
            $ind = $this->indirizzo1;
        }
        if ($this->indirizzo1 && ($this->cap || $this->comune)){
            $ind = $ind." - ";
        }
        if ($this->cap){
            $ind = $ind." ".$this->cap;
        }
        if ($this->comune){
            $ind = $ind." ".$this->comune;
        }
        return $ind;
    }
}