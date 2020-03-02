<?php

namespace App\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class MockRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels, InteractsWithQueue;

    public $tries = 3;
    protected $message, $model;

    /**
     * MessageSent constructor.
     * @param $model
     * @param $message
     */
    public function __construct($model, $message)
    {
        $this->model = $model;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('thread_'.$this->message->thread_id);
    }

    public function broadcastAs()
    {
        return 'client-read';
    }

    public function broadcastWith()
    {
        return [
            'owner_id' => $this->model->id,
            'message_id' => $this->message->id
        ];
    }
}
