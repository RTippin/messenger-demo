<?php
namespace App\Models\Networks;

use App\GhostUser;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\Networks\PendingNetworks
 *
 * @property string $id
 * @property string $sender_id
 * @property string $sender_type
 * @property string $recipient_id
 * @property string $recipient_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\PendingNetworks newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\PendingNetworks newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\PendingNetworks query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\PendingNetworks whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\PendingNetworks whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\PendingNetworks whereRecipientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\PendingNetworks whereRecipientType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\PendingNetworks whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\PendingNetworks whereSenderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Networks\PendingNetworks whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $recipient
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $sender
 */
class PendingNetworks extends Model
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
    protected $table = 'networks_pending';

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function sender()
    {
        return $this->morphTo()->withDefault(function(){
            return new GhostUser();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function recipient()
    {
        return $this->morphTo()->withDefault(function(){
            return new GhostUser();
        });
    }
}
