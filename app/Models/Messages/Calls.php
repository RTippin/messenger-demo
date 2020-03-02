<?php

namespace App\Models\Messages;

use App\GhostUser;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Messages\Calls
 *
 * @property string $id
 * @property string $thread_id
 * @property string $owner_id
 * @property string $owner_type
 * @property int $active
 * @property int $type
 * @property int $mode
 * @property string|null $call_ended
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messages\CallParticipants[] $participants
 * @property-read int|null $participants_count
 * @property-read \App\Models\Messages\Thread $thread
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Calls newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Calls newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Calls query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Calls whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Calls whereCallEnded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Calls whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Calls whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Calls whereMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Calls whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Calls whereOwnerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Calls whereThreadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Calls whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Calls whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $owner
 */
class Calls extends Model
{
    use Uuids;
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
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function owner()
    {
        return $this->morphTo()->withDefault(function(){
            return new GhostUser();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function participants()
    {
        return $this->hasMany(CallParticipants::class, 'call_id');
    }
}
