<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Observers\UserActionsObserver;
use ZeroDaHero\LaravelWorkflow\Traits\WorkflowTrait;
use Spatie\Permission\Traits\HasRoles;
use Workflow;
use Symfony\Component\Workflow\Transition;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
class Convenzione extends \App\Models\BaseEntity
{
    use SoftDeletes;
    use WorkflowTrait;
    use HasRoles;

     //STATI
     const PROPOSTA = 'proposta';
     const APPROVATO = 'approvato';
     const INAPPROVAZIONE = 'inapprovazione';
     const REPERTORIATO = 'repertoriato';

     const da_firmare_direttore = 'da_firmare_direttore';
     const DA_FIRMARE_CONROPARTE2 = 'da_firmare_controparte2';

     const FIRMATO = 'firmato';
    
     //TRANSIZIONI-->AZIONI
     const STORE_TO_INAPPROVAZIONE = 'store_to_inapprovazione';
     const STORE_VALIDAZIONE = 'store_validazione';
     const FIRMA_DA_CONTROPARTE1 = 'firma_da_controparte1';
     const REPERTORIO = 'repertorio';

     const STIPULA_UNIURB = 'uniurb';
     const STIPULA_CONTROPARTE = 'controparte';
     const STIPULA_ANALOGICA = 'cartaceo';
     const STIPULA_DIGITALE = 'digitale';

    protected $guard_name = 'api'; 

     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'convenzioni';

    protected $fillable = [
        'id',
        'descrizione_titolo',
        'schematipotipo',
        'dipartimemto_cd_dip',
        'resp_scientifico',
        'ambito',
        'durata',        
        'prestazioni',
        'corrispettivo',        
        'importo',
        'tipopagamenti_codice',
        'current_place',
        'unitaorganizzativa_uo', //cd_csa dell'utente che crea la convenzione
        'convenzione_type',
        'stipula_type',
        'titolario_classificazione',
        'oggetto_fascicolo',
        'nrecord',
        'numero',
        'num_rep',
        'data_inizio_conv',
        'data_fine_conv',
        'data_stipula',
        'bollo_virtuale',
        'data_sottoscrizione',
        'convenzione_from',
        'rinnovo_type'
    ];

    protected $hidden = ['nrecord'];
    
    protected $appends = ['dipartimento'];
 
    public static function boot()
    {
        parent::boot();        
        Convenzione::observe(new UserActionsObserver());
    }

    public function cacheKey()
    {
        return 'uniconv';
    }

   /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function setCurrentPlaceAttribute($value)
    {
        $this->attributes['current_place'] = $value; 
    }

    public function getCurrentPlaceAttribute()
    {        
        if (array_key_exists('current_place',$this->attributes)){
            return $this->attributes['current_place'];
        }
        return null;
    }


    /**
     * Get the user record associated with the convenzione.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function withUser($user)
    {
        if ($user)
            $this->user_id = $user['id'];        
        else
            $this->user_id = null;

        return $this;
    }

    public function getDipartimentoAttribute()
    {
        return $this->dipartimentoCache();
    }
     /**
     * Get the dipartimento record associated with the convenzione.
     */
    public function dipartimentoRelation()
    {
        return $this->belongsTo('App\Dipartimento','dipartimemto_cd_dip','cd_dip');
    } 
    
    public function dipartimentoCache()
    {
        return Cache::rememberForever($this->cacheKey().'_'.$this->dipartimemto_cd_dip.":dipartimento", function () {
            return $this->dipartimentoRelation()->select(['CD_DIP','nome_dip','nome_breve','dip_id','id_ab'])->get();
        });
    }

    //In your example, if A has a b_id column, then A belongsTo B.
    //If B has an a_id column, then A hasOne or hasMany B depending on how many B should have.
    public function tipopagamento()
    {
        return $this->belongsTo('App\TipoPagamento','tipopagamenti_codice','codice');
    }

    public function scadenze(){
        return $this->hasMany('App\Scadenza','convenzione_id','id');
    }

    public function scadenzeusertasks(){
        
            return $this->hasManyThrough(
                'App\UserTask',
                'App\Scadenza',
                'convenzione_id', // Foreign key on scadenza table...
                'model_id', // Foreign key on UserTask table...
                'id', // Local key on convenzione table...
                'id' // Local key on users table...
            )->where('model_type','App\Scadenza');        
    }

    public function bolli(){
        return $this->hasMany('App\Bollo','convenzioni_id','id');
    }

    public function aziende()
    {
        return $this->belongsToMany('App\AziendaLoc','convenzione_azienda','convenzione_id','azienda_id')->withTimestamps()->withPivot('ordine')->orderBy('convenzione_azienda.ordine');            
    }

    public function listAziendaDenominazione(){
        return $this->aziende->implode('denominazione', ', ');        
    }

    /**
     *  Ritorna l'elenco dei task eseguiti
     */
    public function logtransitions()
    {
        return $this->morphMany(LogTransitions::class, 'model');
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

    public function getWorkflowName(){
        return 'convenzione'; 
    }

 
    public function workflow_transitions_self()
    {               
        if ($this->current_place == null){
            return [];
        }

        $list = Workflow::get($this, $this->getWorkflowName())->getEnabledTransitions($this);
        array_unshift($list, new Transition("self_transition",[$this->current_place],[$this->current_place]));
        return $list;
    }

    public function workflow_transitions() {
        return Workflow::get($this, $this->getWorkflowName())->getEnabledTransitions($this);
    }

    protected $casts = [
        'emission_date' => 'datetime:d-m-Y',
    ];
    
    /**
     * Set attribute to date format
     * @param $input
     */
    public function setDataSottoscrizioneAttribute($input)
    {
        if($input != '') {
            $this->attributes['data_sottoscrizione'] = Carbon::createFromFormat(config('unidem.date_format'), $input)->format('Y-m-d');
        }else{
            $this->attributes['data_sottoscrizione'] = null;
        }
    }

    /**
     * Get attribute from date format
     * @param $input
     *
     * @return string
     */
    public function getDataSottoscrizioneAttribute($input)
    {
        if($input != null && $input != '00-00-0000') {
            return Carbon::createFromFormat('Y-m-d', $input)->format(config('unidem.date_format'));
        }else{
            return '';
        }
    }

     /**
     * Set attribute to date format
     * @param $input
     */
    public function setDataInizioConvAttribute($input)
    {
        if($input != '') {
            $this->attributes['data_inizio_conv'] = Carbon::createFromFormat(config('unidem.date_format'), $input)->format('Y-m-d');
        }else{
            $this->attributes['data_inizio_conv'] = null;
        }
    }

    /**
     * Get attribute from date format
     * @param $input
     *
     * @return string
     */
    public function getDataInizioConvAttribute($input)
    {
        if($input != null && $input != '00-00-0000') {
            return Carbon::createFromFormat('Y-m-d', $input)->format(config('unidem.date_format'));
        }else{
            return null;
        }
    }

      /**
     * Set attribute to date format
     * @param $input
     */
    public function setDataFineConvAttribute($input)
    {
        if($input != '') {
            $this->attributes['data_fine_conv'] = Carbon::createFromFormat(config('unidem.date_format'), $input)->format('Y-m-d');
        }else{
            $this->attributes['data_fine_conv'] = null;
        }
    }

    /**
     * Get attribute from date format
     * @param $input
     *
     * @return string
     */
    public function getDataFineConvAttribute($input)
    {
        if($input != null && $input != '00-00-0000') {
            return Carbon::createFromFormat('Y-m-d', $input)->format(config('unidem.date_format'));
        }else{
            return null;
        }
    }


    /**
     * Set attribute to date format
     * @param $input
     */
    public function setDataStipulaAttribute($input)
    {
        if($input != '') {
            $this->attributes['data_stipula'] = Carbon::createFromFormat(config('unidem.date_format'), $input)->format('Y-m-d');
        }else{
            $this->attributes['data_stipula'] = null;
        }
    }

    /**
     * Get attribute from date format
     * @param $input
     *
     * @return string
     */
    public function getDataStipulaAttribute($input)
    {
        if($input != null && $input != '00-00-0000') {
            return Carbon::createFromFormat('Y-m-d', $input)->format(config('unidem.date_format'));
        }else{
            return null;
        }
    }


    public function bolli_atti_prov(){    
        return $this->bolli()->where('tipobolli_codice','BOLLO_ATTI')->first();
    }

    public function bolli_allegato(){    
        return $this->bolli()->where('tipobolli_codice','BOLLO_ALLEGATI')->first();
    }

    // { value: 'DCD', label: 'Delibera Consiglio di Dipartimento' },
    // { value: 'DDD', label: 'Decreto del direttore di dipartimento' },
    public function tipo_documento_approvazione(){
        $attach = $this->attachments()->whereIn('attachmenttype_codice',['DCD','DDD'])->first();
        if ($attach){
            return __('global.'.$attach->attachmenttype_codice);
        }
    }
    
    public function data_documento_approvazione(){
        $attach = $this->attachments()->whereIn('attachmenttype_codice',['DCD','DDD'])->first();
        if ($attach){
            return $attach->emission_date;
        }
    }

    public function numero_documento_approvazione(){
        $attach = $this->attachments()->whereIn('attachmenttype_codice',['DCD','DDD'])->first();
        if ($attach){
            return $attach->docnumber;
        }
    }
}
