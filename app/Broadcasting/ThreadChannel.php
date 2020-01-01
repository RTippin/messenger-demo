<?php

namespace App\Broadcasting;

use App\Services\Messenger\ThreadService;
use App\User;

class ThreadChannel
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param User $user
     * @param $thread
     * @return array|bool
     */
    public function join(User $user, $thread)
    {
        $model = ChannelRequest::currentProfile($user);
        if($model && ThreadService::AuthThreadSocket($thread, $model)){
            return [
                'slug' => $model->avatar,
                'name' => $model->name,
                'owner_id' => $model->id,
                'online' => $model->isOnline()
            ];
        }
        return null;
    }
}
