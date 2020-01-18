<?php
namespace App;

use App\Traits\HasMessenger;
use App\Traits\Networked;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable
{
    use HasMessenger, Uuids, Networked, SoftDeletes;

    public $incrementing = false;

    public $keyType = 'string';

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token', 'created_at', 'updated_at', 'email', 'active'];

    public function devices()
    {
        return $this->hasMany('App\Models\User\UserDevices');
    }

    public function getNameAttribute()
    {
        return strip_tags(ucwords($this->first." ".$this->last));
    }

    public function getJSNameAttribute()
    {
        return htmlspecialchars(ucwords($this->first." ".$this->last), ENT_QUOTES);
    }

}
