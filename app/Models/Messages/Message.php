<?php

namespace App\Models\Messages;

use App\GhostUser;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Messages\Message
 *
 * @property string $id
 * @property string $thread_id
 * @property string $owner_id
 * @property string $owner_type
 * @property string $body
 * @property int $mtype
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messages\Participant[] $participants
 * @property-read int|null $participants_count
 * @property-read \App\Models\Messages\Thread $thread
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Message newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Message onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Message query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Message whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Message whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Message whereMtype($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Message whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Message whereOwnerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Message whereThreadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Message whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Message withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Message withoutTrashed()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $owner
 */
class Message extends Eloquent
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
    protected $table = 'messages';

    /**
     * The relationships that should be touched on save.
     *
     * @var array
     */
    protected $touches = ['thread'];

    /**
     * The attributes that can be set with Mass Assignment.
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
     * Thread relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     *
     * @codeCoverageIgnore
     */
    public function thread()
    {
        return $this->belongsTo(Thread::class, 'thread_id', 'id');
    }

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
     * Participants relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *
     * @codeCoverageIgnore
     */
    public function participants()
    {
        return $this->hasMany(Participant::class, 'thread_id', 'thread_id');
    }
}
