<?php

return [
    /*
    |--------------------------------------------------------------------------
    | We set your active model and alias at runtime from our service provider
    |--------------------------------------------------------------------------
    |
    | Start with a null model and alias for profile, and auto set using the
    | provided middleware SetMessengerModel, which sets alias as well
    | You may also call set_messenger_profile($model) at any point in
    | pipeline to set/change active model/profile
    |
    */
    'profile' => [
        'model' => null,
        'alias' => null
    ],

    /*
    |--------------------------------------------------------------------------
    | Messenger Model Configuration
    |--------------------------------------------------------------------------
    |
    | List every model you wish to use within this messenger system
    | The name provided will be the alias used for that class for
    | everything including upload folder names, channel names, etc
    | Alias must be lowercase
    |
    | *PLEASE NOTE: Once you choose an alias, you should not change it
    | unless you plan to move the uploads/directory names around yourself
    |
    | To ensure your model receives the notification for friends, add
    | the following method to each of the models class files that you
    | list below, matching the alias you give it
    |
    |    App\SomeCharacter::class
    |
    |    public function receivesBroadcastNotificationsOn()
    |    {
    |        return 'character_notify_'.$this->id;
    |    }
    |
    */
    'models' => [
        'user' => App\User::class,
//        'character' => App\SomeCharacter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable calls
    |--------------------------------------------------------------------------
    |
    | Enable or disable calls. If disabled, front-end will still show
    | the call icon, but will return "Feature currently disabled"
    |
    */
    'calls' => env('CALLS', false),

    /*
    |--------------------------------------------------------------------------
    | Enable mobile push notifications
    |--------------------------------------------------------------------------
    |
    | If enabled, we check user_devices when broadcasting messenger events.
    | If the user has a device, we push to FCM/APN depending if they
    | have a voip token APN(apple) otherwise FCM(google/all)
    |
    */
    'mobile_notify' => env('MOBILE_NOTIFY', false),

];
