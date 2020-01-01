<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserDevices extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'device_id';

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}