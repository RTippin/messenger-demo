<?php

namespace App\Models\Messages;

use App\GhostUser;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class CallParticipants extends Model
{
    use Uuids;

    public $incrementing = false;

    public $keyType = 'string';

    protected $guarded = [];

    public function owner()
    {
        return $this->morphTo()->withDefault(function(){
            return new GhostUser();
        });
    }

    public function call()
    {
        return $this->belongsTo('App\Models\Messages\Call');
    }
}
