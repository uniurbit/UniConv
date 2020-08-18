<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferenteCC extends Model
{

    
     /**
     * Set attribute to date format
     * @param $input
     */
    public function setBirthdateAttribute($input)
    {
        if($input != '') {
            $this->attributes['date'] = Carbon::createFromFormat(config('unidem.date_format'), $input)->format('Y-m-d');
        }else{
            $this->attributes['birthdate'] = '';
        }
    }

    /**
     * Get attribute from date format
     * @param $input
     *
     * @return string
     */
    public function getBirthdateAttribute($input)
    {
        if($input != '00-00-0000') {
            return Carbon::createFromFormat('Y-m-d', $input)->format(config('unidem.date_format'));
        }else{
            return '';
        }
    }


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'referenti_c_c';
}
