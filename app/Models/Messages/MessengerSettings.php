<?php

namespace App\Models\Messages;

use Illuminate\Database\Eloquent\Model;

class MessengerSettings extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'owner_id';
    protected $fillable = ['owner_id', 'owner_type', 'online_status', 'knoks'];

    public function owner()
    {
        return $this->morphTo();
    }
}