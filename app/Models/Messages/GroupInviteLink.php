<?php

namespace App\Models\Messages;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


/**
 * App\Models\Messages\GroupInviteLink
 *
 * @property string $id
 * @property string $thread_id
 * @property string $owner_id
 * @property string $owner_type
 * @property string $slug
 * @property int $max_use
 * @property int $uses
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $owner
 * @property-read \App\Models\Messages\Thread $thread
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\GroupInviteLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\GroupInviteLink newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\GroupInviteLink onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\GroupInviteLink query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\GroupInviteLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\GroupInviteLink whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\GroupInviteLink whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\GroupInviteLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\GroupInviteLink whereMaxUse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\GroupInviteLink whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\GroupInviteLink whereOwnerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\GroupInviteLink whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\GroupInviteLink whereThreadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\GroupInviteLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Messages\GroupInviteLink whereUses($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\GroupInviteLink withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Messages\GroupInviteLink withoutTrashed()
 * @mixin \Eloquent
 */
class GroupInviteLink extends Model
{
    use Uuids, SoftDeletes;

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
    protected $table = 'thread_invites';

    /**
     * @var array
     */
    protected $dates = ['expires_at'];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     *
     */
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $slug = Str::random(6);
            $exist = $model->where(DB::raw('BINARY `slug`'), $slug)->first();
            $model->slug = $exist ? $slug.Str::random(1) : $slug;
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thread()
    {
        return $this->belongsTo(Thread::class, 'thread_id', 'id');
    }
}
