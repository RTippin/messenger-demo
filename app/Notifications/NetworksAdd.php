<?php

namespace App\Notifications;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NetworksAdd extends Notification implements ShouldQueue
{
    use Queueable, Notifiable, InteractsWithQueue;
    protected $network, $action;
    public $tries = 1;
    public function __construct($network, $action)
    {
        $this->network = $network;
        $this->action = $action;
    }

    public function via()
    {
        return ['database', 'broadcast'];
    }

    public function toBroadcast()
    {
        if($this->action){
            return new BroadcastMessage([
                'message' => $this->network->sender->name.' wants to be your friend. You must approve it first'
            ]);
        }
        return new BroadcastMessage([
            'message' => $this->network->party->name.' wants to be your friend. You auto approved the request'
        ]);
    }

    public function toArray()
    {
        return [
            'owner_id' => ($this->action ? $this->network->sender_id : $this->network->party_id),
            'owner_type' => ($this->action ? $this->network->sender_type : $this->network->party_type),
            'action' => $this->action,
        ];
    }
}