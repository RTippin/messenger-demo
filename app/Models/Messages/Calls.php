<?php

namespace App\Models\Messages;

use App\GhostUser;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Calls extends Model
{
    use Uuids;
    public $incrementing = false;

    public function owner()
    {
        return $this->morphTo()->withDefault(function(){
            return new GhostUser();
        });
    }

    public function thread()
    {
        return $this->belongsTo('App\Models\Messages\Thread');
    }

    public function participants()
    {
        return $this->hasMany('App\Models\Messages\CallParticipants', 'call_id');
    }
}
