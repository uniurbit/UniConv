<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Http\Controllers\Api\V1\QueryBuilder;
use App\Ruolo;
use App\UnitaOrganizzativa;
use App\MappingUfficio;
use Illuminate\Support\Facades\Cache;

class Personale extends Model
{    

    protected $connection = 'oracle';    

    public $table = 'V_IE_RU_PERSONALE';
    public $primaryKey = 'ID_AB';

    public $selectcolumns = array('nome','cognome', 'matricola', 'aff_org', 'email','cd_ruolo'. 'id_ab','cod_fis');

    public static function boot()
    {
        parent::boot();
        static::addGlobalScope('Fetch', function ($builder) {
            $builder->select(['nome','cognome', 'matricola', 'aff_org', 'email','cd_ruolo', 'V_IE_RU_PERSONALE.id_ab as id_ab','cod_fis']);
        });
    }

    public function ruolo()
    {
        return $this->belongsTo(Ruolo::class,'cd_ruolo','ruolo');
    }

    public function cacheKey()
    {
        return sprintf(
            "%s/%s",
            $this->getTable(),
            $this->aff_org
        );
    }
        
    public function unitaRelation()
    {
        return Cache::remember($this->cacheKey() . ':unita', 60 * 24 * 20, function () {
            return is_null($this->unita()->get()) ? false : $this->unita()->get();
        });
    }

    public function unita()
    {
        return $this->belongsTo(UnitaOrganizzativa::class,'aff_org','uo');
    }    

    public function mappingufficio()
    {
        return $this->belongsTo(MappingUfficio::class,'aff_org','unitaorganizzativa_uo');
    } 


    public function scopeFindByIdAB($query, $id_ab)
    {        
        return $query->where('id_ab',$id_ab);
    }


    // restituisce un persona cercandola dalla sua email
    public function scopeFindByEmail($query, $email)
    {        
        return $query->where('email',$email);
    }

    public function isDocente()
    {
        return $this->ruolo->isDocente();
    }

    public function isPta()
    {
        return $this->ruolo->isPta();
    }

    public function scopeFindByAfferenzaOrganizzativa($query, $uo)
    {        
        return $query->where('aff_org',$uo);
    }
    
    /** restituisce il nome utente ricercabile su titulus */
    public function getUtenteNomepersonaAttribute(){
        return strtolower($this->attributes['cognome']).' '.strtolower($this->attributes['nome']);
    }
}