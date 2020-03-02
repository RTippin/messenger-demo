<?php

namespace App\Traits;

use App\Models\Messages\Calls;
use App\Models\Messages\Message;
use App\Models\Messages\Messenger;
use App\Models\Messages\Participant;
use App\Models\Messages\Thread;
use Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use DB;

trait HasMessenger
{

    /**
     * Messenger Settings relationship.
     *
     * @return MorphOne
     *
     */
    public function messenger()
    {
        return $this->morphOne(Messenger::class, 'owner');
    }

    /**
     * Message relationship.
     *
     * @return MorphMany
     *
     */
    public function messages()
    {
        return $this->morphMany(Message::class, 'owner');
    }

    /**
     * Participants relationship.
     *
     * @return HasMany
     *
     * @codeCoverageIgnore
     */
    public function participants()
    {
        return $this->morphMany(Participant::class, 'owner');
    }

    /**
     * Thread relationship.
     *
     * @return BelongsToMany
     *
     * @codeCoverageIgnore
     */
    public function threads()
    {
        return $this->belongsToMany(
            Thread::class,
            'participants',
            'owner_id',
            'thread_id'
        )->whereNull('participants.deleted_at')
        ->where('owner_type', get_class($this))
        ->latest('updated_at');
    }

    /**
     * @return Builder|BelongsToMany
     */
    public function ongoingCalls()
    {
        return $this->threads()
            ->has('activeCall')
            ->with('activeCall.participants');
    }

    /**
     * @return mixed
     */
    public function calls()
    {
        return $this->belongsToMany(
            Calls::class,
            'call_participants',
            'owner_id',
            'call_id'
        )->latest('updated_at');
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

    /**
     * @return int
     */
    public function unreadThreadsCount()
    {
        return $this->threadsWithNewMessages()->count();
    }

    /**
     * @return Collection
     */
    public function threadsWithNewMessages()
    {
        return $this->threads()->where(function (Builder $q) {
                $q->whereNull('participants.last_read');
                $q->orWhere('threads.updated_at', '>', DB::raw('participants.last_read'));
            })->get();
    }

    /**
     * @return string
     */
    public function getAvatarAttribute()
    {
        return $this->avatar();
    }

    /**
     * @param bool $full
     * @return string
     */
    public function avatar($full = false)
    {
        $alias = get_messenger_alias($this);
        if($alias){
            return route('profile_img', [$alias, $this->slug(), ($full ? 'full' : 'thumb'), ($this->messenger->picture ? $this->messenger->picture : 'users.png')], false);
        }
        return route('profile_img', ['ghost', 'ghost', 'thumb', 'users.png'], false);
    }

    /**
     * @param bool $full
     * @return string
     */
    public function slug($full = false)
    {
        return $full ? route('model_profile', [get_messenger_alias($this->messenger->owner), $this->messenger->slug], false) : $this->messenger->slug;
    }

    /**
     * @return int
     */
    public function getOnlineStatusNumberAttribute()
    {
        return $this->isOnline();
    }

    /**
     * @return int
     */
    public function isOnline()
    {
        $online = Cache::has(get_messenger_alias($this).'_online_'.$this->id);
        $away = Cache::has(get_messenger_alias($this).'_away_'.$this->id);
        if($this->messenger->online_status === 0){
            return 0;
        }
        if($online && $this->messenger->online_status === 2){
            return 2;
        }

        return ($online ? 1 : ($away ? 2 : 0));
    }

    /**
     * @return string
     */
    public function onlineStatus()
    {
        $online = Cache::has(get_messenger_alias($this).'_online_'.$this->id);
        $away = Cache::has(get_messenger_alias($this).'_away_'.$this->id);
        if($this->messenger->online_status === 0){
            return 'offline';
        }
        if($online && $this->messenger->online_status === 2){
            return 'away';
        }
        return ($online ? 'online' : ($away ? 'away' : 'offline'));
    }
}