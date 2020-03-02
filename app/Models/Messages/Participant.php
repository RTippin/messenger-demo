<?php

namespace App\Models\Messages;

use App\GhostUser;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Messages\Participant
 *
 * @property string $id
 * @property string $thread_id
 * @property string $owner_id
 * @property string $owner_type
 * @property int $admin
 * @property \Illuminate\Support\Carbon|null $last_read
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Messages\Thread $thread
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Participant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Participant newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Participant onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Participant query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Participant whereAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Participant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Participant whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Participant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Participant whereLastRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Participant whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Participant whereOwnerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Participant whereThreadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\Participant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Participant withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\Participant withoutTrashed()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $owner
 */
class Participant extends Eloquent
{
    use SoftDeletes, Uuids;
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
    protected $table = 'participants';

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
    protected $dates = ['deleted_at', 'last_read'];

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
}
