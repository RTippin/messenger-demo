<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use RTippin\Messenger\Contracts\MessengerProvider;
use RTippin\Messenger\Contracts\Searchable;
use RTippin\Messenger\Traits\Messageable;
use RTippin\Messenger\Traits\Search;

/**
 * App\Models\User
 *
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string|null $avatar
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static Builder|User demo()
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MessengerProvider, Searchable
{
    use Uuids;

    use Messageable; //comes with messenger package

    use Search; //comes with messenger package

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email'
    ];

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    public $keyType = 'string';

    /**
     * The column name your providers avatar is stored in the database as.
     *
     * @return string
     */
    public function getAvatarColumn(): string
    {
        return 'avatar';
    }

    /**
     * Scope a query for only demo users.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDemo(Builder $query): Builder
    {
        return $query->where('demo', '=', 1);
    }
}
