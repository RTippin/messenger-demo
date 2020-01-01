<?php
namespace App;
use App\Models\Messages\MessengerSettings;
use App\Models\User\UserInfo;
use Illuminate\Database\Eloquent\Model as Eloquent;

class GhostUser extends Eloquent
{
    protected $guarded = [];
    protected $attributes = [
        'id' => '12345678-aaaa-4321-9df7-c8296b601234',
        'firstName' => 'Ghost',
        'lastName' => 'User',
        'email' => 'ghost@ghost.com'
    ];

    public function messengerSettings()
    {
        return $this->newBelongsTo($this->newQuery(), $this, '', '', '')->withDefault(function(){
            return new MessengerSettings([
                'owner_id' => $this->id,
                'owner_type' => 'App\User',
                'online_status' => 0,
                'knoks' => 0
            ]);
        });
    }

    public function getAvatarAttribute()
    {
        return $this->avatar();
    }

    public function avatar($full = false)
    {
        return route('profile_img', ['ghost', ($full ? 'full' : 'thumb'), 'users.png'], false);
    }

    public function getOnlineStatusNumberAttribute()
    {
        return $this->isOnline();
    }

    public function isOnline()
    {
        return 0;
    }

    public function onlineStatus()
    {
        return 'offline';
    }

    public function receivesBroadcastNotificationsOn()
    {
        return 'user_notify_'.$this->id;
    }

    public function devices()
    {
        return $this->newHasMany($this->newQuery(), $this, '', '');
    }

    public function slug($full = false)
    {
        return $full ? route('user_profile', 'ghost', false) : 'ghost';
    }

    public function getNameAttribute()
    {
        return "Ghost User";
    }

    public function getJSNameAttribute()
    {
        return "Ghost User";
    }

    public function info()
    {
        return $this->newBelongsTo($this->newQuery(), $this, '', '', '')->withDefault(function(){
            return new UserInfo([
                'user_id' => $this->id,
                'slug' => 'ghost'
            ]);
        });
    }
}
