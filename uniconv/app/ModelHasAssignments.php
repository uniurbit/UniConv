<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModelHasAssignments  extends Model
{
    public $table = 'model_has_assignments';
    protected $primaryKey = ['v_ie_ru_personale_id_ab','model_id', 'model_type'];

    public $incrementing = false;
    public $timestamps = false;
    
    protected $fillable = ['v_ie_ru_personale_id_ab', 'model_id', 'model_type', 'cd_tipo_posizorg'];

    protected $appends = ['nome_utente'];
    
    /**
 * Set the keys for a save update query.
 *
 * @param  \Illuminate\Database\Eloquent\Builder  $query
 * @return \Illuminate\Database\Eloquent\Builder
 */
protected function setKeysForSaveQuery(\Illuminate\Database\Eloquent\Builder $query)
{
    $keys = $this->getKeyName();
    if(!is_array($keys)){
        return parent::setKeysForSaveQuery($query);
    }

    foreach($keys as $keyName){
        $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
    }

    return $query;
}

    /**
     * Get the primary key value for a save query.
     *
     * @param mixed $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if(is_null($keyName)){
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function scopeRespons($query){
        //COOR_PRO_D //RESP_DID
        return $query->where('cd_tipo_posizorg', 'RESP_UFF');
    }

    public function personale()
    {
        return $this->belongsTo(Personale::class,'v_ie_ru_personale_id_ab','id_ab');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'v_ie_ru_personale_id_ab','v_ie_ru_personale_id_ab');
    }

    public function getNomeUtenteAttribute()
    {
        if ($this->user){
            return $this->user->name;
        }
        return null;
    }
}
