<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Brexis\LaravelWorkflow\Traits\WorkflowTrait;
use Workflow;
use Symfony\Component\Workflow\Transition;

class Scadenza extends Model
{
    
    const ATTIVO = 'attivo';
    const EMESSA = 'emesso';
    const INEMISSIONE = 'inemissione';    
    const INPAGAMENTO = 'inpagamento';
    CONST PAGATO = 'pagato';

    const ORDINEINCASSO = 'ordineincasso';
    const REGISTRAZIONEPAGAMENTO = 'registrazionepagamento';    

    const EMISSIONE = 'emissione';

  
    use WorkflowTrait;

    public $table = 'scadenze';

    protected $fillable = [
            'id',
            'convenzione_id',
            'data_tranche',
            'dovuto_tranche',
            'tipo_emissione',
            'data_emisrichiesta',
            'protnum_emisrichiesta',
            'data_fattura',            
            'num_fattura',
            'data_ordincasso',
            'num_ordincasso',
            'prelievo',
            'note'
    ];


    public function workflow_transitions_self()
    {
        $list = Workflow::get($this)->getEnabledTransitions($this);
        array_unshift($list, new Transition("self_transition",[$this->state],[$this->state]));
        return $list;
    }

    /**
     * Set attribute to date format
     * @param $input
     */
    public function setDataTrancheAttribute($input)
    {
        if($input != '') {
            $this->attributes['data_tranche'] = Carbon::createFromFormat(config('unidem.date_format'), $input)->format('Y-m-d');
        }else{
            $this->attributes['data_tranche'] = '';
        }
    }

    /**
     * Get attribute from date format
     * @param $input
     *
     * @return string
     */
    public function getDataTrancheAttribute($input)
    {
        if($input != null && $input != '00-00-0000') {
            return Carbon::createFromFormat('Y-m-d', $input)->format(config('unidem.date_format'));
        }else{
            return null;
        }
    }

  /**
     * Set attribute to date format data_emisrichiesta
     * @param $input
     */
    public function setDataEmisrichiestaAttribute($input)
    {
        if($input != '') {
            $this->attributes['data_emisrichiesta'] = Carbon::createFromFormat(config('unidem.date_format'), $input)->format('Y-m-d');
        }else{
            $this->attributes['data_emisrichiesta'] = null;
        }
    }

    /**
     * Get attribute from date format
     * @param $input
     *
     * @return string
     */
    public function getDataEmisrichiestaAttribute($input)
    {
        if($input != null && $input != '00-00-0000') {
            return Carbon::createFromFormat('Y-m-d', $input)->format(config('unidem.date_format'));
        }else{
            return null;
        }
    }


    /**
     * Set attribute to date format data_fattura
     * @param $input
     */
    public function setDataFatturaAttribute($input)
    {
        if($input != '') {
            $this->attributes['data_fattura'] = Carbon::createFromFormat(config('unidem.date_format'), $input)->format('Y-m-d');
        }else{
            $this->attributes['data_fattura'] = null;
        }
    }

    /**
     * Get attribute from date format
     * @param $input
     *
     * @return string
     */
    public function getDataFatturaAttribute($input)
    {
        if($input != null && $input != '00-00-0000') {
            return Carbon::createFromFormat('Y-m-d', $input)->format(config('unidem.date_format'));
        }else{
            return null;
        }
    }


    
    /**
     * Set attribute to date format data_ordincasso
     * @param $input
     */
    public function setDataOrdincassoAttribute($input)
    {
        if($input != '') {
            $this->attributes['data_ordincasso'] = Carbon::createFromFormat(config('unidem.date_format'), $input)->format('Y-m-d');
        }else{
            $this->attributes['data_ordincasso'] = null;
        }
    }

    /**
     * Get attribute from date format
     * @param $input
     *
     * @return string
     */
    public function getDataOrdincassoAttribute($input)
    {
        if($input != null && $input != '00-00-0000') {
            return Carbon::createFromFormat('Y-m-d', $input)->format(config('unidem.date_format'));
        }else{
            return null;
        }
    }

    public function convenzione()
    {
        return $this->belongsTo(Convenzione::class,'convenzione_id','id');
    }

    public function getWorkflowName(){
        return 'scadenza'; 
    }

    /**
     * Get the attachments relation morphed to the current model class
     *
     * @return MorphMany
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'model')->AttachmentType();
    }

    
    /**
     *  Ritorna l'elenco degli user task associati
     */
    public function usertasks()
    {
        return $this->morphMany(UserTask::class, 'model');
    }

    public function aziende()
    {
        $relation = $this->belongsToMany('App\AziendaLoc','convenzione_azienda','convenzione_id','azienda_id','convenzione_id','id');        
        return $relation; 
    }
   
}
