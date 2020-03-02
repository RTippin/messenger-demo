<?php
namespace App\Models\Networks;

use App\GhostUser;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Networks\Networks
 *
 * @property string $id
 * @property string $owner_id
 * @property string $owner_type
 * @property string $party_id
 * @property string $party_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\Networks newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\Networks newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\Networks query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\Networks whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\Networks whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\Networks whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\Networks whereOwnerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\Networks wherePartyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\Networks wherePartyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\Networks whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $owner
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $party
 */
class Networks extends Model
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
    protected $table = 'networks';

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
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function party()
    {
        return $this->morphTo()->withDefault(function(){
            return new GhostUser();
        });
    }
}
