<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class CallStarted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels, InteractsWithQueue;

    public $tries = 3;
    protected $data, $channels;

    /**
     * MessageSent constructor.
     * @param $data
     * @param $channels
     */
    public function __construct($data, $channels)
    {
        $this->data = $data;
        $this->channels = $channels;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return $this->channels;
    }

    public function broadcastAs()
    {
        return 'call_started';
    }

    public function broadcastWith()
    {
        return $this->data;
    }
}
