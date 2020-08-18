<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Http\Controllers\Api\V1\QueryBuilder;

class Ruolo extends Model
{
    protected $connection = 'oracle';    

    public $table = 'V_RUOLI';
    public $primaryKey = 'RUOLO';

    const DOCENTITYPE = array(9,1,11);
    const PTATYPE = array(3);

     // Allow for camelCased attribute access
     public function getAttribute($key)
     {
        return parent::getAttribute(snake_case($key));
     }
 
     public function setAttribute($key, $value)
     {
        return parent::setAttribute(snake_case($key), $value);
     }     

     public function isDocente()
     {
        return in_array($this->tipoRuolo, self::DOCENTITYPE);
     }
     public function isPta()
     {
        return in_array($this->tipoRuolo, self::PTATYPE);
     }
}
