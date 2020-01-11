<?php

namespace App\Broadcasting;

use App\Services\Messenger\CallService;
use App\User;

class CallChannel
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
     * @param $call
     * @return array|bool
     */
    public function join(User $user, $thread, $call)
    {
        if(messenger_profile() && CallService::AuthCallSocket($thread, $call)){
            return [
                'avatar' => messenger_profile()->avatar,
                'name' => messenger_profile()->name,
                'owner_id' => messenger_profile()->id
            ];
        }
        return null;
    }
}
