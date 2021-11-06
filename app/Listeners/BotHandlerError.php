<?php

namespace App\Listeners;

use RTippin\Messenger\Events\BotActionFailedEvent;

class BotHandlerError
{
    /**
     * Handle the event.
     *
     * @param  BotActionFailedEvent  $event
     * @return void
     */
    public function handle(BotActionFailedEvent $event): void
    {
        report($event->exception);
    }
}
