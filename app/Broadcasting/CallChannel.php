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
        $model = ChannelRequest::currentProfile($user);
        if($model && CallService::AuthCallSocket($thread, $call, $model)){
            return [
                'avatar' => $model->avatar,
                'name' => $model->name,
                'owner_id' => $model->id
            ];
        }
        return null;
    }
}
