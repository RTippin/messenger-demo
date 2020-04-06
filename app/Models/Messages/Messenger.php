<?php

namespace App\Models\Messages;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Messages\Messenger
 *
 * @property string $owner_id
 * @property string $owner_type
 * @property string $slug
 * @property string|null $picture
 * @property int $message_popups
 * @property int $message_sound
 * @property int $call_ringtone_sound
 * @property int $knoks
 * @property int $calls_outside_networks
 * @property int $online_status
 * @property string|null $ip
 * @property string|null $timezone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $owner
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereCallRingtoneSound($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereCallsOutsideNetworks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereKnoks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereMessagePopups($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereMessageSound($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereOnlineStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereOwnerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $notify_sound
 * @property int $friend_approval
 * @property int $dark_mode
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereDarkMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereFriendApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Messenger whereNotifySound($value)
 */
class Messenger extends Model
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
     * @var string
     */
    protected $primaryKey = 'owner_id';

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function owner()
    {
        return $this->morphTo();
    }
}
