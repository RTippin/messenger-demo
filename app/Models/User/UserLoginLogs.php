<?php

namespace App\Models\User;

use App\Traits\Uuids;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\User\UserLoginLogs
 *
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLoginLogs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLoginLogs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLoginLogs query()
 * @mixin \Eloquent
 * @property string $id
 * @property string $user_id
 * @property string|null $ip
 * @property string|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLoginLogs whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLoginLogs whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLoginLogs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLoginLogs whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLoginLogs whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserLoginLogs whereUserId($value)
 */
class UserLoginLogs extends Model
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
     * @var string
     */
    protected $table = 'user_login_logs';

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
