<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Http\Controllers\Api\V1\QueryBuilder;
use App\Dipartimento;
use App\Service\RoleService;
use Illuminate\Support\Facades\Cache;
class UnitaOrganizzativa extends Model
{
    protected $connection = 'oracle';    

    public $table = 'VISTA_ORG_ATTIVA';
    public $primaryKey = 'ID_AB';

    protected $dates = [        
        'data_fin'        
    ];

    protected $casts = [
        'data_fin' => 'datetime:d-m-Y',
    ];
      
     public function dipartimento()
     {
        if ($this->isDipartimento()){
            return Cache::remember($this->cacheKey() . ':dipartimento', 60 * 24 * 20, function () {
                return is_null($this->belongsTo(Dipartimento::class,'id_ab','dip_id')->get()) ? false : $this->belongsTo(Dipartimento::class,'id_ab','dip_id')->get();
            });            
        }                
     }

     public function isPlesso(){
        return $this->tipo == 'PLD';
     }

     public function isDipartimento(){
        return $this->tipo == 'DIP';
     }

 /**
     * restituisce un array dei dipartimenti 
     * che afferiscono all'unità organizzativa corrente.
     *
     * @return Array
     */
    public function dipartimenti_cd_dip(){
        if ($this->isPlesso()){
            //Plesso Economico - Umanistico (DESP-DISTUM)
            if ($this->id_ab == 26618){
                return [21, 8]; //[26121,4504];
            }
            //Plesso Giuridico-Umanistico (DIGIUR-DISCUI)
            if ($this->id_ab == 26616){
                return [1,25]; //[26124,4499,49025];
            }
            //Plesso Scientifico (DiSPeA-DiSB)
            if ($this->id_ab == 32718){
                return[20,23]; //[26080,27605];
            }     
            //... aggiungere ulteriori associazioni      
        }
    }


    public function cacheKey()
    {
        return sprintf(
            "%s/%s",
            $this->getTable(),
            $this->id_ab
        );
    }
             
    public function dipartimenti(){
        if ($this->isPlesso()){
            //Plesso Economico - Umanistico (DESP-DISTUM)
            if ($this->id_ab == 26618){
                return Cache::remember($this->cacheKey() . ':dipartimenti', 60 * 24 * 20, function () {
                    return Dipartimento::whereIn('dip_id',[26121,4504])->get(); 
                });                
            }
            //Plesso Giuridico-Umanistico (DIGIUR-DISCUI) //26124
            if ($this->id_ab == 26616){
                return Cache::remember($this->cacheKey() . ':dipartimenti', 60 * 24 * 20, function () {
                    return Dipartimento::whereIn('dip_id',[4499,49025])->get(); 
                });                
            }
            //Plesso Scientifico (DiSPeA-DiSB)
            if ($this->id_ab == 32718){
                return Cache::remember($this->cacheKey() . ':dipartimenti', 60 * 24 * 20, function () {
                    return Dipartimento::whereIn('dip_id',[26080,27605])->get(); 
                });                
            }           
        }
    }

    public function dipartimenti_uo(){
        if ($this->isPlesso()){
            //Plesso Economico - Umanistico (DESP-DISTUM)
            if ($this->id_ab == 26618){
                return ['004424','004939'];
            }
            //Plesso Giuridico-Umanistico (DIGIUR-DISCUI)
            if ($this->id_ab == 26616){
                return ['004419','004940','005579'];
            }
            //Plesso Scientifico (DiSPeA-DiSB)
            if ($this->id_ab == 32718){
                return ['004919','005019'];
            }           
        } else {
            return [$this->uo];
        }
    }



     public function isUnitaSuperAdmin(){
        
        if (in_array($this->uo, config('unidem.unitaSuperAdmin')))
            return true;

        return false;
     }


     public function isUnitaAdmin(){
        
        if (in_array($this->uo, config('unidem.unitaAdmin')))
            return true;

        return false;
     }

     public function isUffFiscale(){
        
        if (in_array($this->uo, config('unidem.uffFiscale')))
            return true;

        return false;
     }

    public function isUfficiPerValidazione(){
        if (in_array($this->uo, config('unidem.ufficiPerValidazione')))
            return true;

        return false;
     }

    public function scopeUfficiRuoli($query){
        $uoByRoleList = MappingRuolo::whereHas('role', function($q){
            $q->whereIn('name',['admin','admin_amm','op_contabilita','op_approvazione']);
        })->get()->pluck('unitaorganizzativa_uo')->toArray();
        return $query->whereIn('uo', $uoByRoleList); 
        //array_merge(array_merge(config('unidem.unitaAdmin'), config('unidem.ufficiPerValidazione')),config('unidem.uffFiscale')));
    }

    public function scopeUfficiValidazione($query){
         return $query->whereIn('uo', config('unidem.ufficiPerValidazione'));
    }

    public function scopeUfficiFiscali($query){
        return $query->whereIn('uo', config('unidem.uffFiscale'));
    }

    public function scopeUfficiAdmin($query){
        return $query->whereIn('uo', config('unidem.unitaAdmin'));
    }

     public function organico()
     {
         return $this->hasMany(Organico::class, 'id_ab_uo',  'id_ab');         
     }

     public function personale()
     {
         return $this->hasMany(Personale::class, 'aff_org',  'uo');         
     }
}
