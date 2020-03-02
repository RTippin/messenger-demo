<?php

namespace App\Models\User;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\User\UserDevices
 *
 * @property string|null $user_id
 * @property string $device_id
 * @property string $device_token
 * @property string|null $voip_token
 * @property int|null $badges
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserDevices newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserDevices newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserDevices query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserDevices whereBadges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserDevices whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserDevices whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserDevices whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserDevices whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserDevices whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserDevices whereVoipToken($value)
 * @mixin \Eloquent
 */
class UserDevices extends Model
{
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
     * @var string
     */
    protected $primaryKey = 'device_id';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
