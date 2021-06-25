<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Weather bot API key
    |--------------------------------------------------------------------------
    |
    | When registering the weather bot, you must obtain an API key from:
    | https://www.weatherapi.com/
    */
    'weather_api_key' => env('BOT_WEATHER_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | IP-PI key
    |--------------------------------------------------------------------------
    |
    | When registering the location bot, you can opt to use the paid (pro)
    | endpoint from ip-api. To obtain a key, visit:
    | https://ip-api.com/
    */
    'ip_api_key' => env('BOT_LOCATION_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Random image endpoint
    |--------------------------------------------------------------------------
    |
    | When registering the random image bot, we will download an image from
    | the specified URL. Unsplash is used as default.
    */
    'random_image_url' => env('BOT_RANDOM_IMAGE_URL', 'https://source.unsplash.com/random'),
];
