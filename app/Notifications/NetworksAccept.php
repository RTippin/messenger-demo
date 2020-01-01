<?php

namespace App\Notifications;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NetworksAccept extends Notification implements ShouldQueue
{
    use Queueable, Notifiable, InteractsWithQueue;
    protected $network, $action;
    public $tries = 1;
    public function __construct($network)
    {
        $this->network = $network;
    }

    public function via()
    {
        return ['database', 'broadcast'];
    }

    public function toBroadcast()
    {
        return new BroadcastMessage([
            'message' => $this->network->party->name.' accepted your friend request. You are now connected'
        ]);
    }

    public function toArray()
    {
        return [
            'owner_id' => $this->network->party_id,
            'owner_type' => $this->network->party_type
        ];
    }
}