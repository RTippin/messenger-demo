<?php

namespace App\Providers;

use App\Bots\RecursionBot;
use App\Brokers\JanusBroker;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use RTippin\Messenger\Facades\Messenger;
use RTippin\Messenger\Facades\MessengerBots;

/**
 * Laravel Messenger System.
 * Created by: Richard Tippin.
 *
 * @link https://github.com/RTippin/messenger
 * @link https://github.com/RTippin/messenger-bots
 * @link https://github.com/RTippin/messenger-faker
 * @link https://github.com/RTippin/messenger-ui
 */
class MessengerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Messenger::registerProviders([
            User::class,
        ]);

        // Set the video driver of your choosing.
//        Messenger::setVideoDriver(JanusBroker::class);

        // Register the bot handlers you wish to use.
        MessengerBots::registerHandlers([
            RecursionBot::class,
        ]);
    }
}
