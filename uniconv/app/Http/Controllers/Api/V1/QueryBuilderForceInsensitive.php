<?php
namespace App\Http\Controllers\Api\V1;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator as BasePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Exceptions\UnknownColumnException;
use App\FindParameter;
use Carbon\Carbon;

class QueryBuilderForceInsensitive extends QueryBuilder
{

    protected function addWhereToQuery($where)
    {        
        //extract($where);
        // For array values (whereIn, whereNotIn)
        if (isset($where->values)) {
            $value = $values;
        }
        if (!isset($where->operator)) {
            $operator = '';
        }
        /** @var mixed $key */
        if ($this->isExcludedParameter($where->field)) {
            return;
        }        
        if ($this->hasCustomFilter($where->field)) {
            /** @var string $type */
            return $this->applyCustomFilter($where->field, $where->operator,  $where->value,  $where->type);
        }
        //TODO se la connessione Oracle va saltato
        // if (!$this->hasTableColumn($where->field)) {
        //    throw new UnknownColumnException("Unknown column '{$where->field}'");
        // }
        if ($where->type == 'date'){
            $where->value = Carbon::createFromFormat(config('unidem.date_format'), $where->value)->format('Y-m-d');;
        }

        /** @var string $type */
        if ($where->operator == 'In') {
            $this->query->whereIn($where->field, $where->value);
        } else if ( $where->operator == 'NotIn') {
            $this->query->whereNotIn($where->field, $where->value);
        }
        else if ( $where->operator == 'contains') {
            $this->query->whereRaw('upper('.$where->field.') like ?', '%'.strtoupper($where->value).'%');
        } else {
            if ( $where->value == '[null]') {
                if ( $where->operator == '=') {
                    $this->query->whereNull($where->field);
                } else {
                    $this->query->whereNotNull($where->field);
                }
            } else {
                if ($where->type == 'string'){
                    $this->query->whereRaw('upper('.$where->field.') '. $where->operator .' ?', strtoupper($where->value));
                }else{
                    $this->query->where($where->field, $where->operator, $where->value);
                }
            }
        }
    }

}

