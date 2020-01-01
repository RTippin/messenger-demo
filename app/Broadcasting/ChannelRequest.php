<?php

namespace App\Broadcasting;

class ChannelRequest
{
    public static function currentProfile($user)
    {
        return $user;
    }
}
