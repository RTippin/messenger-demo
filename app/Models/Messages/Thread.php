<?php

namespace App\Models\Messages;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Messages\Thread
 *
 * @property string $id
 * @property int $ttype
 * @property string|null $subject
 * @property string|null $image
 * @property int $add_participants
 * @property int $admin_call
 * @property int $send_message
 * @property int $lockout
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Messages\Calls $activeCall
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messages\Calls[] $calls
 * @property-read int|null $calls_count
 * @property-read mixed $avatar
 * @property-read mixed $name
 * @property-read \App\Models\Messages\GroupInviteLink $groupInviteLink
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messages\Message[] $messages
 * @property-read int|null $messages_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messages\Participant[] $participants
 * @property-read int|null $participants_count
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereAddParticipants($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereAdminCall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereLockout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereSendMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereTtype($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Thread whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Thread withoutTrashed()
 * @mixin \Eloquent
 */
class Thread extends Eloquent
{
    use SoftDeletes,
        Uuids;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    public $keyType = 'string';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'threads';

    /**
     * The attributes that can't be set with Mass Assignment.
     *
     * @var array
     */
    protected $guarded = [];


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    /**
     * @return string
     */
    public function getAvatarAttribute()
    {
        return $this->avatar();
    }

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        $name = 'Profile';
        if($this->ttype === 1){
            $party = $this->otherParty();
            $name = $party ? $party->owner->name : $name;
        }
        else if($this->ttype === 2){
            $name = $this->subject;
        }
        return strip_tags(ucwords($name));
    }

    /**
     * @return string
     */
    public function avatar()
    {
        return route('group_avatar', ['thread_id' => $this->id, 'thumb' => null, 'image' => $this->image], false);
    }

    /**
     * Messages relationship.
     *
     * @return HasMany
     *
     * @codeCoverageIgnore
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'thread_id', 'id');
    }

    /**
     * Return most recent message from thread with owner relation
     * @return Eloquent|HasMany|object|null
     */
    public function latestMessage()
    {
        return $this->messages()->with('owner')->latest()->first();
    }

    /**
     * @return HasMany
     */
    public function participants()
    {
        return $this->hasMany(Participant::class, 'thread_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function calls()
    {
        return $this->hasMany(Calls::class, 'thread_id');
    }

    /**
     * @return HasOne
     */
    public function activeCall()
    {
        return $this->hasOne(Calls::class, 'thread_id')->where('active', 1);
    }

    /**
     * @return HasOne
     */
    public function groupInviteLink()
    {
        return $this->hasOne(GroupInviteLink::class);
    }

    /**
     * @return mixed|null
     */
    public function otherParty()
    {
        return messenger_profile() ? $this->participants->where('owner_id', '!=', messenger_profile()->id)->first() : null;
    }
}
