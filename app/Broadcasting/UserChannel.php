<?php

namespace App\Broadcasting;

use App\User;

class UserChannel
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
     * @param \App\User $user
     * @param $id
     * @return array|bool
     */
    public function join(User $user, $id)
    {
        return $user->id === $id;
    }
}
