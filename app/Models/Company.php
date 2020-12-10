<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use RTippin\Messenger\Contracts\MessengerProvider;
use RTippin\Messenger\Contracts\Searchable;
use RTippin\Messenger\Traits\Messageable;

/**
 * App\Models\Company
 *
 * @method static Builder|Company newModelQuery()
 * @method static Builder|Company newQuery()
 * @method static Builder|Company query()
 * @mixin \Eloquent
 * @property int $id
 * @property string $company_name
 * @property string $company_email
 * @property string|null $avatar
 * @property int $admin
 * @property int $demo
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Company whereAdmin($value)
 * @method static Builder|Company whereAvatar($value)
 * @method static Builder|Company whereCompanyEmail($value)
 * @method static Builder|Company whereCompanyName($value)
 * @method static Builder|Company whereCreatedAt($value)
 * @method static Builder|Company whereDemo($value)
 * @method static Builder|Company whereId($value)
 * @method static Builder|Company wherePassword($value)
 * @method static Builder|Company whereRememberToken($value)
 * @method static Builder|Company whereUpdatedAt($value)
 */
class Company extends Authenticatable implements MessengerProvider, Searchable
{
    use Uuids;

    use Messageable; //comes with messenger package

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
        'company_email'
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
     * Format and return your provider name here.
     * ex: $this->first . ' ' . $this->last.
     *
     * @return string
     */
    public function name(): string
    {
        return strip_tags(ucwords($this->company_name));
    }

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
     * Search for companies
     *
     * @param Builder $query
     * @param string $search
     * @param array $searchItems
     * @return Builder
     */
    public static function getProviderSearchableBuilder(Builder $query,
                                                        string $search,
                                                        array $searchItems): Builder
    {
        return $query->where(function (Builder $query) use ($searchItems) {
            foreach ($searchItems as $item) {
                $query->orWhere('company_name', 'LIKE', "%{$item}%");
            }
        })->orWhere('company_email', '=', $search);
    }
}
