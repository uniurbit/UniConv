<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; 
use App\Observers\UserActionsObserver;
use App\Models\BaseEntity;

class Submission extends BaseEntity
{
  
    public static $rules = [
        'name' => 'required', 
        'surname' => 'required', 
        'gender' => ['required','regex:/^[mM]$|^[fF]$/'], 
        'fiscalcode' => 'required', //|min:16 
        'birthplace' => 'required', 
        'birthprovince' => 'required', 
        'birthdate' => 'required', 
        'com_res' => 'required',
    ];			

    /**
    * The attributes that should be mutated to dates.
    *
    * @var array
    */
    protected $dates = ['deleted_at'];
    
    protected $fillable = [
          'name',
          'surname',
          'gender',
          'fiscalcode',
          'birthplace',
          'birthprovince',
          'birthdate',
          'com_res',
          'prov_res',
          'via_res',
          'civ_res',
          'presso',
          'pe_accesso',
          'dt_inquadra',
          'cate_inquadra',
          'pe_inquadra',
          'af_inquadra',
          'user_id',
          'data',
          'filename',
          'filename_txt',
          'status'

    ];
    

    public static function boot()
    {
        parent::boot();
        
        Submission::observe(new UserActionsObserver);
    }
    
    
    /**
     * Set attribute to date format
     * @param $input
     */
    public function setBirthdateAttribute($input)
    {
        if($input != '') {
            $this->attributes['birthdate'] = Carbon::createFromFormat(config('unidem.date_format'), $input)->format('Y-m-d');
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



    public function assigments()
    {
        return $this->hasMany('App\Assignment','submission_id');
    }

    public function trainings()
    {
        return $this->hasMany('App\Training');
    }

   

    public function partecipations()
    {
        return $this->hasMany('App\Partecipation');
    }

    public function proftitles()
    {
        return $this->hasMany('App\ProfTitle');
    }

    public function pubblications()
    {
        return $this->hasMany('App\Pubblication');
    }

    public function lectures()
    {
        return $this->hasMany('App\Lecture');
    }

    public function studies()
    {
        return $this->hasMany('App\Study');
    }

    public function qualifications()
    {
        return $this->hasMany('App\Qualification');
    }

}
