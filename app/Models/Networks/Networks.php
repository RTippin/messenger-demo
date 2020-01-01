<?php
namespace App\Models\Networks;

use App\GhostUser;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Networks extends Model
{
    use Uuids;
    public $incrementing = false;
    protected $table = 'networks';
    protected $fillable = ['owner_id', 'owner_type','party_id', 'party_type','status'];

    public function owner()
    {
        return $this->morphTo()->withDefault(function(){
            return new GhostUser();
        });
    }

    public function party()
    {
        return $this->morphTo()->withDefault(function(){
            return new GhostUser();
        });
    }
}