<?php

namespace App\Models\Messages;

use App\Traits\Uuids;
use Auth;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thread extends Eloquent
{
    use SoftDeletes, Uuids;
    public $incrementing = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'threads';

    /**
     * The attributes that can't be set with Mass Assignment.
     *
     * @var array
     */
    protected $guarded = [];


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    public function getAvatarAttribute()
    {
        return $this->avatar();
    }

    public function getNameAttribute()
    {
        $name = 'Profile';
        if($this->ttype === 1){
            $party = $this->otherParty();
            $name = $party ? $party->owner->name : $name;
        }
        else if($this->ttype === 2){
            $name = $this->subject;
        }
        return strip_tags(ucwords($name));
    }

    public function avatar($api = false)
    {
        return ($api ? route('group_avatar_api', ['thread_id' => $this->id, 'thumb' => null, 'image' => $this->image], false) : route('group_avatar', ['thread_id' => $this->id, 'thumb' => null, 'image' => $this->image], false));
    }

    /**
     * Messages relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *
     * @codeCoverageIgnore
     */
    public function messages()
    {
        return $this->hasMany('App\Models\Messages\Message', 'thread_id', 'id');
    }

    public function participants()
    {
        return $this->hasMany('App\Models\Messages\Participant', 'thread_id', 'id');
    }

    public function calls()
    {
        return $this->hasMany('App\Models\Messages\Calls', 'thread_id');
    }

    public function activeCall()
    {
        return $this->hasOne('App\Models\Messages\Calls', 'thread_id')->where('active', 1);
    }

    public function groupInviteLink()
    {
        return $this->hasOne('App\Models\Messages\GroupInviteLink');
    }

    public function otherParty()
    {
        $id = session()->get('business_Session') ? session()->get('business_Session') : Auth::id();
        return $this->participants->where('owner_id', '!=', $id)->first();
    }
}
