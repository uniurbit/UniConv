<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Comune;

class Provincia extends Model
{
    protected $connection = 'oracle';    

    public $table = 'PROVINCE';
    public $primaryKey = 'COD';

    protected $dates = [
        'data_ins',
        'data_mod'        
    ];

     // Allow for camelCased attribute access
     public function getAttribute($key)
     {
         return parent::getAttribute(snake_case($key));
     }
 
     public function setAttribute($key, $value)
     {
         return parent::setAttribute(snake_case($key), $value);
     }

    /**
     * The roles that belong to the user.
     */
    public function comuni()
    {
        return $this->belongsToMany(Comune::class, 'COMUNE_PROV', 'PROVINCIA','COD');
    }


    public function getCODAttribute() {

        return $this->attributes['cod'];

    }

    public function getc_o_dAttribute() {

        return $this->attributes['cod'];

    }

 
}
