<?php
namespace App\Models\Networks;

use App\GhostUser;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;

class Networks extends Model
{
    use Uuids;
    public $incrementing = false;

    public $keyType = 'string';

    protected $table = 'networks';

    protected $guarded = [];

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
