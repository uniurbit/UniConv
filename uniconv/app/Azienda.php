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

    public $table = 'IE01_ANAGRAFICHE';
    public $primaryKey = 'ID_ESTERNO';

    protected $fillable = ['id_esterno', 'cd_tipo_ab' ,'nome','cognome', 'denominazione', 'cod_fis', 'part_iva', 'rappresentante_legale'];

    public static function boot()
    {
        parent::boot();
        static::addGlobalScope('Fetch', function ($builder) {
            $builder->select(['id_esterno', 'cd_tipo_ab' ,'nome','cognome', 'denominazione', 'cod_fis', 'part_iva', 'rappresentante_legale']);
        });
    }


    public function indirizzi()
    {
        return $this->hasMany(IndirizzoAzienda::class, 'id_esterno', 'id_esterno')->valido();
    }


    public function indirizzo_residenza()
    {
        return $this->hasOne(IndirizzoAzienda::class, 'id_esterno', 'id_esterno')->valido()->residenza();
    }

    public function getDenominazioneAttribute($value)
    {
        return  ucwords(strtolower($value));
    }

}
