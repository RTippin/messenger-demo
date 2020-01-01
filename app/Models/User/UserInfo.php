<?php
namespace App\Models\User;

use App\GhostUser;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'user_id';
    protected $table = 'user_info';
    protected $fillable = ['slug', 'picture'];

    public function user()
    {
        return $this->belongsTo('App\User')->withDefault(function(){
            return new GhostUser();
        });
    }

    public function portfolio()
    {
        return $this->hasOne('App\Models\User\UserPortfolio', 'user_id');
    }
}
