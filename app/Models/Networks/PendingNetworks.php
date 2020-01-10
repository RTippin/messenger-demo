<?php
namespace App\Models\Networks;

use App\GhostUser;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;


class PendingNetworks extends Model
{
    use Uuids;
    public $incrementing = false;
    public $keyType = 'string';
    protected $table = 'networks_pending';
    protected $fillable = ['sender_id', 'sender_type','recipient_id', 'recipient_type'];

    public function sender()
    {
        return $this->morphTo()->withDefault(function(){
            return new GhostUser();
        });
    }

    public function recipient()
    {
        return $this->morphTo()->withDefault(function(){
            return new GhostUser();
        });
    }
}
