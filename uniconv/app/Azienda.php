<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\IndirizzoAzienda;

//"Codice alfanumerico del tipo di enetità. Valori ammessi:
//- PF:Persone fisiche,
//- SC: Soggetti collettivi,
//- DI: Ditte individuali,
//- UO: Unità organizzative"

// ID_ESTERNO
// CD_TIPO_AB
// COGNOME
// NOME
// DENOMINAZIONE
// COD_FIS
// PART_IVA
// COD_FIS_ESTERO
// PART_IVA_ESTERO

// RAPPRESENTANTE_LEGALE

class Azienda extends Model
{
    protected $connection = 'oracle';    

    public $table = 'V_IE_AC_AB_ALL'; //'IE01_ANAGRAFICHE'; //'V_IE_AC_AB_ALL'
    public $primaryKey = 'id_ab';

    //protected $fillable = ['id_esterno', 'cd_tipo_ab' ,'nome','cognome', 'denominazione', 'cod_fis', 'part_iva', 'rappresentante_legale'];

    protected $fillable =[
        'cd_sdi',
        'id_ab',
        'cd_cia',
        'cd_tipo_ab',
        'cognome',
        'nome',
        'denominazione',
        'cod_fis',
        'part_iva',
        'dt_nascita',
        'ds_comune',
        'ds_nazione',
        'rappresentante_legale',
        'cd_tipo_sogcoll',
        'ds_tipo_sogcoll'
    ];
    

    public static function boot()
    {
        parent::boot();
        static::addGlobalScope('Fetch', function ($builder) {
            $builder->select(['cd_sdi',
            'id_ab',
            'cd_cia',
            'cd_tipo_ab',
            'cognome',
            'nome',
            'denominazione',
            'cod_fis',
            'part_iva',
            'dt_nascita',
            'ds_comune',
            'ds_nazione',
            'rappresentante_legale',
            'cd_tipo_sogcoll',
            'ds_tipo_sogcoll']);
        });
    }


    public function indirizzi()
    {
        return $this->hasMany(IndirizzoAzienda::class, 'id_ab', 'id_ab')->valido();
    }


    public function indirizzo_residenza()
    {
        return $this->hasOne(IndirizzoAzienda::class, 'id_ab', 'id_ab')->valido();
    }

    public function getDenominazioneAttribute($value)
    {
        return  ucwords(mb_strtolower($value));
    }

}
