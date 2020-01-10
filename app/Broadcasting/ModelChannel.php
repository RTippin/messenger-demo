<?php

namespace App\Broadcasting;

use App\User;

class ModelChannel
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
     * @param $alias
     * @param $id
     * @return array|bool
     */
    public function join(User $user, $alias, $id)
    {
        return messenger_alias() === $alias && messenger_profile()->id === $id;
    }
}
