<?php
namespace App;

use App\Models\User\UserDevices;
use App\Models\User\UserLoginLogs;
use App\Traits\HasMessenger;
use App\Traits\Networked;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;


/**
 * App\User
 *
 * @property string $id
 * @property string $first
 * @property string $last
 * @property string $email
 * @property int $active
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messages\Calls[] $calls
 * @property-read int|null $calls_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User\UserDevices[] $devices
 * @property-read int|null $devices_count
 * @property-read mixed $avatar
 * @property-read mixed $j_s_name
 * @property-read mixed $name
 * @property-read mixed $online_status_number
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User\UserLoginLogs[] $loginLogs
 * @property-read int|null $login_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messages\Message[] $messages
 * @property-read int|null $messages_count
 * @property-read \App\Models\Messages\Messenger $messenger
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Networks\Networks[] $networks
 * @property-read int|null $networks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messages\Participant[] $participants
 * @property-read int|null $participants_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Networks\PendingNetworks[] $pendingReceivedNetworks
 * @property-read int|null $pending_received_networks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Networks\PendingNetworks[] $pendingSentNetworks
 * @property-read int|null $pending_sent_networks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messages\Thread[] $threads
 * @property-read int|null $threads_count
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereFirst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLast($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\User withoutTrashed()
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasMessenger,
        Uuids,
        Networked,
        SoftDeletes;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    public $keyType = 'string';

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'created_at', 'updated_at', 'email', 'active'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function devices()
    {
        return $this->hasMany(UserDevices::class);
    }

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        return strip_tags(ucwords($this->first." ".$this->last));
    }

    /**
     * @return string
     */
    public function getJSNameAttribute()
    {
        return htmlspecialchars(ucwords($this->first." ".$this->last), ENT_QUOTES);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function loginLogs()
    {
        return $this->hasMany(UserLoginLogs::class);
    }

}
