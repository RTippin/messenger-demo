<?php

namespace App\Providers;

use App\Listeners\BotHandlerError;
use App\Listeners\BroadcastError;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use RTippin\Messenger\Events\BotActionFailedEvent;
use RTippin\Messenger\Events\BroadcastFailedEvent;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        BotActionFailedEvent::class => [
            BotHandlerError::class,
        ],
        BroadcastFailedEvent::class => [
            BroadcastError::class,
        ],
    ];
}
