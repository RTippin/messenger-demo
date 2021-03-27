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
    ],
];
