<?php
namespace App;

use App\Traits\HasMessenger;
use App\Traits\Networked;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable
{
    use Notifiable, HasMessenger, Uuids, Networked, SoftDeletes;

    public $incrementing = false;
    public $keyType = 'string';
    protected $fillable = ['first', 'last' , 'email' , 'password', 'active'];
    protected $hidden = ['password', 'remember_token', 'created_at', 'updated_at', 'email', 'active'];

    public function receivesBroadcastNotificationsOn()
    {
        return 'user_notify_'.$this->id;
    }

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
