<?php
namespace App;

use App\Traits\Networked;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\Messagable;


class User extends Authenticatable
{
    use Notifiable, Messagable, Uuids, Networked, SoftDeletes;

    public $incrementing = false;
    protected $fillable = ['firstName', 'lastName' , 'email' , 'password', 'active'];
    protected $hidden = ['password', 'remember_token', 'created_at', 'updated_at', 'email', 'active'];

    public function receivesBroadcastNotificationsOn()
    {
        return 'user_notify_'.$this->id;
    }

    public function devices()
    {
        return $this->hasMany('App\Models\User\UserDevices');
    }

    public function slug($full = false)
    {
        return $full ? route('user_profile', $this->info->slug, false) : $this->info->slug;
    }

    public function getNameAttribute()
    {
        return strip_tags(ucwords($this->firstName." ".$this->lastName));
    }

    public function getJSNameAttribute()
    {
        return htmlspecialchars(ucwords($this->firstName." ".$this->lastName), ENT_QUOTES);
    }

    public function info()
    {
        return $this->hasOne('App\Models\User\UserInfo');
    }

}
