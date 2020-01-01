<?php

namespace App\Traits;

use App\User;
use Cache;
use Illuminate\Database\Eloquent\Builder;

trait Messagable
{

    /**
     * Messenger Settings relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     *
     */
    public function messengerSettings()
    {
        return $this->morphOne('App\Models\Messages\MessengerSettings', 'owner');
    }

    public function getOnlineStatusNumberAttribute()
    {
        return $this->isOnline();
    }

    public function isOnline()
    {
        $online = Cache::has(strtolower(class_basename($this)).'_online_'.$this->id);
        $away = Cache::has(strtolower(class_basename($this)).'_away_'.$this->id);
        if($this->messengerSettings->online_status === 0){
            return 0;
        }
        if($online && $this->messengerSettings->online_status === 2){
            return 2;
        }

        return ($online ? 1 : ($away ? 2 : 0));
    }

    public function onlineStatus()
    {
        $online = Cache::has(strtolower(class_basename($this)).'_online_'.$this->id);
        $away = Cache::has(strtolower(class_basename($this)).'_away_'.$this->id);
        if($this->messengerSettings->online_status === 0){
            return 'offline';
        }
        if($online && $this->messengerSettings->online_status === 2){
            return 'away';
        }

        return ($online ? 'online' : ($away ? 'away' : 'offline'));
    }

    /**
     * Message relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     *
     */
    public function messages()
    {
        return $this->morphMany('App\Models\Messages\Message', 'owner');
    }

    /**
     * Participants relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *
     * @codeCoverageIgnore
     */
    public function participants()
    {
        return $this->morphMany('App\Models\Messages\Participant', 'owner');
    }

    /**
     * Thread relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     *
     * @codeCoverageIgnore
     */
    public function threads()
    {
        return $this->belongsToMany(
            'App\Models\Messages\Thread',
            'participants',
            'owner_id',
            'thread_id'
        )->whereNull('participants.deleted_at')
        ->where('owner_type', get_class($this))
        ->latest('updated_at');
    }

    public function getAvatarAttribute()
    {
        return $this->avatar();
    }

    public function ongoingCalls()
    {
        return $this->threads()->has('activeCall')->with('activeCall.participants');
    }

    public function calls()
    {
        return $this->belongsToMany(
            'App\Models\Messages\Calls',
            'call_participants',
            'owner_id',
            'call_id'
        )->latest('updated_at');
    }

    public function avatar($full = false)
    {
        $type = class_basename($this);
        switch($type){
            case "User":
                return route('profile_img', [$this->slug(), ($full ? 'full' : 'thumb'), ($this->info->picture ? $this->info->picture : 'users.png')], false);
            break;
        }
        return route('profile_img', ['ghost', ($full ? 'full' : 'thumb'), 'users.png'], false);
    }

    /**
     * Returns the new messages count for user.
     *
     * @return int
     */
    public function newThreadsCount()
    {
        return $this->threadsWithNewMessages()->count();
    }

    public function unreadThreadsCount()
    {
        return $this->threadsWithNewMessages()->count();
    }

    /**
     * Returns all threads with new messages.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function threadsWithNewMessages()
    {
        return $this->threads()->where(function (Builder $q) {
                $q->whereNull('participants.last_read');
                $q->orWhere('threads.updated_at', '>', $this->getConnection()->raw($this->getConnection()->getTablePrefix() . 'participants.last_read'));
            })->get();
    }
}
