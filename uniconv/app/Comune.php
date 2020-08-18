<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Http\Controllers\Api\V1\QueryBuilder;
class Comune extends Model
{
    protected $connection = 'oracle';    

    public $table = 'COMUNE';
    public $primaryKey = 'COD';

    protected $dates = [
        'data_in',
        'data_fin'        
    ];

     public function scopeComuni($query)
     {
         return $query->where('DATA_FIN', '>=',  Carbon::now());
     }
 
     public function scopeOnlyDescr($query)
     {
         return $query->select(['cod','descr']);
     }
 
     public function getDescrAttribute($value)
     {
         return  ucwords(strtolower($value));
     }

     public function provincie()
     {
         return $this->belongsToMany('App\Provincia', 'COMUNE_PROV', 'COD', 'PROVINCIA');
     }

     public static function paginateQuery(FindParameter $parameters){      
        $class = 'App\Comune';
        $queryBuilder = new QueryBuilder(new $class(), null, $parameters);                
        return $queryBuilder->build()->paginate();             
    }
 
}
