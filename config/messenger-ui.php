<?php

return [
    /*
    |--------------------------------------------------------------------------
    | The name of your application
    |--------------------------------------------------------------------------
    |
    */
    'site_name' => env('MESSENGER_SITE_NAME', 'Messenger'),

    /*
    |--------------------------------------------------------------------------
    | Endpoint our javascript will use for socket.io
    |--------------------------------------------------------------------------
    |
    */
    'socket_endpoint' => env('MESSENGER_SOCKET_ENDPOINT', config('app.url')),

    /*
    |--------------------------------------------------------------------------
    | Messenger-UI web routes config
    |--------------------------------------------------------------------------
    |
    | Invite view / redemption routes for both web and api have individual
    | middleware control so you may allow both guest or authed users to
    | access.
    |
    | *For the broadcasting channels to register, you must have already
    | setup/defined your laravel apps broadcast driver.
    |
    */
    'routing' => [
        'domain' => null,
        'prefix' => 'messenger',
        'middleware' => ['web', 'auth', 'messenger.provider'],
        'invite_middleware' => ['web', 'auth.optional', 'messenger.provider'],
    ],
];
