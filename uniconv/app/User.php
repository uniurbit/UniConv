<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Observers\UserActionsObserver;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

use App;
class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','name', 'email', 'password', 'v_ie_ru_personale_id_ab', 'blocked_date'
    ];

    protected $casts = [
        'blocked_date' => 'datetime:d-m-Y',        
    ];

    protected $appends = ['listaruoli'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function boot()
    {
        parent::boot();
        
        User::observe(new UserActionsObserver);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'email'           => $this->email,
            'roles'           => $this->roles()->pluck('name')->map(function ($value) {
                                    return Str::upper($value);
                                }),   
            'permissions'     => $this->getAllPermissions()->pluck('name')->filter(function ($value, $key) {
                return Str::startsWith($value,'ui');
            })                  
        ];
    }

    public function getIntendedUrl(){
        if (App::environment('local')) {
            return config('unidem.client_url'); 
        }
        return config('unidem.client_url');         
    }
    

    /**
     * Set attribute to date format
     * @param $input
     */
    public function setBlockedDateAttribute($input)
    {
        if($input != '') {
            $this->attributes['blocked_date'] = Carbon::createFromFormat(config('unidem.date_format'), $input)->format('Y-m-d');
        }else{
            $this->attributes['blocked_date'] = null;
        }
    }

    /**
     * Get attribute from date format
     * @param $input
     *
     * @return string
     */
    public function getBlockedDateAttribute($input)
    {
        if($input != null && $input != '00-00-0000') {
            return Carbon::createFromFormat('Y-m-d', $input)->format(config('unidem.date_format'));
        }else{
            return null;
        }
    }



    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }

    //nei casi ad interim NON è un hasOne ma un hasMany aggiungo filtro di afferenza organizzativa
    public function personaleRespons()    
    {
        return $this->hasMany(PersonaleResponsOrg::class, 'id_ab', 'v_ie_ru_personale_id_ab');
    }

    public function myselfPersonaleRespons()
    {
        return $this->hasOne(PersonaleResponsOrg::class, 'id_ab_resp', 'v_ie_ru_personale_id_ab');
    }
        
    public function cacheKey()
    {
        return sprintf(
            "%s/%s",
            $this->getTable(),
            $this->v_ie_ru_personale_id_ab
        );
    }
        
    public function personaleRelation()
    {
        return Cache::remember($this->cacheKey() . ':personale', 60 * 24 * 20, function () {
            return is_null($this->personale()->get()) ? false : $this->personale()->get();
        });
    }

    public function personale()
    {
        return $this->hasOne(Personale::class, 'id_ab', 'v_ie_ru_personale_id_ab');
    }

    //non corretto
    public function codice_unitaorganizzativa()
    {
        return $this->findPersonaleRespons()->cd_csa;                                    
    }

    public function unitaOrganizzativa()
    {
        return $this->personaleRelation()->first()->unitaRelation()->first();                
    }
      


    public function personaleAfferenzeOrganizzative()
    {
        $uos = $this->personaleRespons()->get();
        if ($uos->count() > 0) {
            return $uos;
        }
        $myself = $this->myselfPersonaleRespons()->first();
        if ($myself->cd_tipo_posizorg_resp==PersonaleResponsOrg::RESP_PLESSO){
            $myself = $this->copyRespInfo($myself);
            return collect([$myself]);
        }
        return collect([]);
    }    

    /**
     * codiciUnitaorganizzative 
     *
     * @return Array di codici unitaorganizzative
     */
    public function codiciUnitaorganizzative() : Array
    {
        $uos = $this->personaleAfferenzeOrganizzative()->pluck('cd_csa')->toArray();
        return $uos;                
    }    

    /**
     * responsabile: Funzione che restituisce il l'oggetto "Personale" del responsabile
     *
     * @return PersonaleResponsOrg
     */
    public function responsabile(){
        $pers =  $this->findPersonaleRespons(); 
        if ($pers)
            return $pers->responsabile()->first();        
    }

    public function getListaruoliAttribute(){

        if ($this->roles == null || $this->roles->count() == 0){
            return "Nessun ruolo";
        }else{                        
            return $this->roles->implode('name',', ');            
        }        
    }
    
    /**
     * findPersonaleRespons è il record che restituisce la tabella PersonaleResponsOrg con id_ab = v_ie_ru_personale_id_ab 
     * con indicato il ruolo del "Personale" e il suo responsabile
     *
     * @return PersonaleResponsOrg 
     */
    public function findPersonaleRespons(): PersonaleResponsOrg {
        if ($this->personaleRespons()->first()){
            $aff_org = $this->personaleRelation()->first()->aff_org;
            return $this->personaleRespons()->where('cd_csa',$aff_org)->first();
        }else{
            $myself = $this->myselfPersonaleRespons()->first();
            if ($myself->cd_tipo_posizorg_resp==PersonaleResponsOrg::RESP_PLESSO){
                $myself = $this->copyRespInfo($myself);
                return $myself;
            }
            return null;
        }
    }

    private function copyRespInfo($myself)
    {
        $myself->nome = $myself->nome_resp;
        $myself->cognome = $myself->cognome_resp;
        $myself->id_ab = $myself->id_ab_resp;
        $myself->cd_tipo_posizorg = $myself->cd_tipo_posizorg_resp;
        $myself->cd_csa = $myself->cd_csa_resp;
        return $myself;
    }
}

