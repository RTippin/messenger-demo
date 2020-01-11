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
        if(messenger_profile() && ThreadService::AuthThreadSocket($thread)){
            return [
                'slug' => messenger_profile()->avatar,
                'name' => messenger_profile()->name,
                'owner_id' => messenger_profile()->id,
                'online' => messenger_profile()->isOnline()
            ];
        }
        return null;
    }
}
