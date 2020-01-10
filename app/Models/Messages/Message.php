<?php

namespace App\Models\Messages;

use App\GhostUser;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Eloquent
{
    use SoftDeletes, Uuids;
    public $incrementing = false;

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
        return $this->belongsTo('App\Models\Messages\Thread', 'thread_id', 'id');
    }

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
        return $this->hasMany('App\Models\Messages\Participant', 'thread_id', 'thread_id');
    }
}
