<?php
namespace App;
use App\Models\Messages\Messenger;
use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * App\GhostUser
 *
 * @property-read mixed $avatar
 * @property-read mixed $j_s_name
 * @property-read mixed $name
 * @property-read mixed $online_status_number
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GhostUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GhostUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GhostUser query()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\GhostUser[] $devices
 * @property-read int|null $devices_count
 * @property-read \App\GhostUser $messenger
 */
class GhostUser extends Eloquent
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string
     */
    public $keyType = 'string';

    /**
     * @var array
     */
    protected $attributes = [
        'id' => '12345678-aaaa-4321-9df7-c8296b601234',
        'first' => 'Ghost',
        'last' => 'User',
        'email' => 'ghost@tippindev.com'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function messenger()
    {
        return $this->newBelongsTo($this->newQuery(), $this, '', '', '')->withDefault(function(){
            return new Messenger([
                'owner_id' => $this->id,
                'owner_type' => 'App\User',
                'slug' => 'ghost',
                'online_status' => 0,
                'knoks' => 0
            ]);
        });
    }

    /**
     * @return string
     */
    public function getAvatarAttribute()
    {
        return $this->avatar();
    }

    /**
     * @param bool $full
     * @return string
     */
    public function avatar($full = false)
    {
        return route('profile_img', ['ghost', ($full ? 'full' : 'thumb'), 'users.png'], false);
    }

    /**
     * @return int
     */
    public function getOnlineStatusNumberAttribute()
    {
        return $this->isOnline();
    }

    /**
     * @return int
     */
    public function isOnline()
    {
        return 0;
    }

    /**
     * @return string
     */
    public function onlineStatus()
    {
        return 'offline';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function devices()
    {
        return $this->newHasMany($this->newQuery(), $this, '', '');
    }

    /**
     * @param bool $full
     * @return string
     */
    public function slug($full = false)
    {
        return $full ? route('model_profile', 'ghost', false) : 'ghost';
    }

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        return "Ghost User";
    }

    /**
     * @return string
     */
    public function getJSNameAttribute()
    {
        return "Ghost User";
    }
}
