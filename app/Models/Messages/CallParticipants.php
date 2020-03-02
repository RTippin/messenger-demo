<?php

namespace App\Models\Messages;

use App\GhostUser;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Messages\CallParticipants
 *
 * @property string $id
 * @property string $call_id
 * @property string $owner_id
 * @property string $owner_type
 * @property string|null $left_call
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Messages\Calls $call
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\CallParticipants newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\CallParticipants newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\CallParticipants query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\CallParticipants whereCallId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\CallParticipants whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\CallParticipants whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\CallParticipants whereLeftCall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\CallParticipants whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\CallParticipants whereOwnerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\CallParticipants whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $owner
 */
class CallParticipants extends Model
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
    public function call()
    {
        return $this->belongsTo(Calls::class);
    }
}
