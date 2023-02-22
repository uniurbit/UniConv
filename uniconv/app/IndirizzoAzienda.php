<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Comune;

//Codice del tipo di indirizzo. Valori ammessi:
// - FIS: Domicilio fiscale
// - RES: Residenza/Sede legale
// - DOM: Domicilio
// - LAV: Sede di lavoro
// - ALT: Altro

class IndirizzoAzienda extends Model
{
    protected $connection = 'oracle';    

    public $table = 'V_IE_AC_SC_BASE';
    public $primaryKey = 'id_ab';

    protected $fillable = ['id_ab','dt_fine_val','indirizzo', 'cd_cap','cd_catasto_comune','ds_comune','num_civico','cd_sigla_prov'];

    public static function boot()
    {
        parent::boot();
        static::addGlobalScope('Fetch', function ($builder) {
            //'id_ab','dt_fine_val','indirizzo', 'cd_cap','cd_catasto_comune','ds_comune','num_civico','cd_cap',
            $builder->select(['id_ab','dt_fine_val','indirizzo','cd_cap','cd_catasto_comune','ds_comune','num_civico','cd_sigla_prov']);
        });
    }

    public function comune(){
        return $this->hasOne(Comune::class, 'cod', 'cd_catasto_comune')->onlyDescr();
    }

    public function scopeValido($query)
    {
        return $query->where('dt_fine_val', '>=',  Carbon::now());
    }

    // public function scopeResidenza($query)
    // {
    //     return $query->with('comune')->where('cd_tipo_ind', 'RES');
    // }

    public function getIndirizzoAttribute($value)
    {
        return  ucwords(mb_strtolower($value));
    }

}
