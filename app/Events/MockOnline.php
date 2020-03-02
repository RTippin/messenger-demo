<?php

namespace App\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class MockOnline implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels, InteractsWithQueue;

    public $tries = 3;
    protected $thread, $model;

    /**
     * MessageSent constructor.
     * @param $model
     * @param $thread
     */
    public function __construct($model, $thread)
    {
        $this->model = $model;
        $this->thread = $thread;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('thread_'.$this->thread->id);
    }

    public function broadcastAs()
    {
        return 'client-online';
    }

    public function broadcastWith()
    {
        return [
            'owner_id' => $this->model->id,
            'name' => $this->model->name,
            'online' => 1
        ];
    }
}
