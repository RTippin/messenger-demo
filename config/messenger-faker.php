<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Local file locations
    |--------------------------------------------------------------------------
    |
    | When seeding image/document/audio messages, we will choose a random file
    | from the directory you specify below for each message type.
    |
    | * Default image seeder command pulls images from unsplash API.
    |
    */
    'paths' => [
        'images' => storage_path('faker/images'),
        'documents' => storage_path('faker/documents'),
        'audio' => storage_path('faker/audio'),
        'videos' => storage_path('faker/videos'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default URL to download faker image messages.
    |--------------------------------------------------------------------------
    */
    'default_image_url' => 'https://source.unsplash.com/random',

    /*
    |--------------------------------------------------------------------------
    | Enable or disable registering our included FakerBot.
    |--------------------------------------------------------------------------
    */
    'enable_bot' => true,
];
