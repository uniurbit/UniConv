<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogTransitions extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'logtransitions';

    protected $fillable = ['transition_leave','user_id'];

    protected $casts = [
        'updated_at' => 'datetime:d-m-Y H:m',
    ];  
    
    public function model()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
