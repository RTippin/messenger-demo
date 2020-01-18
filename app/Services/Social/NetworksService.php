<?php

namespace App\Services\Social;
use App\Events\FriendAccept;
use App\Events\FriendAdd;
use App\Events\FriendDenied;
use App\Models\Messages\Messenger;
use App\Models\Networks\Networks;
use App\Models\Networks\PendingNetworks;
use App\Services\Messenger\MessengerRepo;
use Illuminate\Http\Request;
use Exception;

class NetworksService
{
    protected $party, $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handleActions()
    {
        $this->partyModel();
        if(!$this->party || $this->party->id === messenger_profile()->id){
            return array("state" => false, "error" => "Unable to locate party");
        }
        switch($this->request->input('action')){
            case 'add':
                return $this->addToNetwork();
            break;
            case 'remove':
                return $this->removeNetworks();
            break;
            case 'cancel':
                return $this->cancelNetworkRequest();
            break;
            case 'accept':
                return $this->acceptNetworkRequest();
            break;
            case 'deny':
                return $this->denyNetworkRequest();
            break;
        }
        return array("state" => false, "error" => "Invalid action");
    }

    public static function MakeNetworkRequest()
    {
        $friends = collect([]);
        try{
            messenger_profile()->pendingReceivedNetworks->reverse()->each(function ($friend) use($friends){
                $friends->push([
                    'id' => $friend->id,
                    'owner_id' => $friend->sender->id,
                    'name' => $friend->sender->name,
                    'slug' => $friend->sender->slug(),
                    'avatar' => $friend->sender->avatar,
                    'type' => get_messenger_alias($friend->sender),
                    'created_at' => $friend->created_at->toDateTimeString(),
                    'locale_created_at' => MessengerRepo::FormatDateTimezone($friend->created_at)->toDateTimeString()
                ]);
            });
        }catch (Exception $e){
            report($e);
        }
        return $friends;
    }

    private function exist()
    {
        return messenger_profile()->networkStatus($this->party);
    }

    private function partyModel()
    {
        $slug = Messenger::where('slug', $this->request->input('slug'))->first();
        $this->party = ($slug ? $slug->owner : null);
    }

    private function addToNetwork()
    {
        if($this->exist() !== 0){
            return array("state" => false, "error" => "Unable to proceed");
        }
        PendingNetworks::firstOrCreate([
            'sender_id' => messenger_profile()->id,
            'sender_type' => get_class(messenger_profile()),
            'recipient_id' => $this->party->id,
            'recipient_type' => get_class($this->party)
        ]);
        $this->broadcastFriendAdd();
        return array("state" => true, "msg" => "You sent a friend request to ".$this->party->name, "case" => 2);
    }

    private function cancelNetworkRequest()
    {
        if($this->exist() !== 2){
            return array("state" => false, "error" => "Unable to proceed");
        }
        messenger_profile()->pendingSentNetworks()->where('recipient_id', $this->party->id)->where('recipient_type', get_class($this->party))->delete();
        return array("state" => true, "msg" => "Removed friend request to ".$this->party->name, "case" => 0);
    }

    private function acceptNetworkRequest()
    {
        if($this->exist() !== 3){
            return array("state" => false, "error" => "Unable to proceed");
        }
        $this->party->pendingSentNetworks()->where('recipient_id', messenger_profile()->id)->where('recipient_type', get_class(messenger_profile()))->delete();
        return $this->joinNetworks();
    }

    private function denyNetworkRequest()
    {
        if($this->exist() !== 3){
            return array("state" => false, "error" => "Unable to proceed");
        }
        $this->party->pendingSentNetworks()->where('recipient_id', messenger_profile()->id)->where('recipient_type', get_class(messenger_profile()))->delete();
        $this->broadcastFriendDenied();
        return array("state" => true, "msg" => "You denied the friend request from ".$this->party->name, "case" => 0);
    }

    private function removeNetworks()
    {
        if($this->exist() !== 1){
            return array("state" => false, "error" => "Unable to proceed");
        }
        messenger_profile()->networks()->where('party_id', $this->party->id)->where('party_type', get_class($this->party))->delete();
        $this->party->networks()->where('party_id', messenger_profile()->id)->where('party_type', get_class(messenger_profile()))->delete();
        return array("state" => true, "msg" => "You have removed ".$this->party->name." from your friends", "case" => 0);
    }

    private function joinNetworks()
    {
        Networks::firstOrCreate([
            'owner_id' => messenger_profile()->id,
            'owner_type' => get_class(messenger_profile()),
            'party_id' => $this->party->id,
            'party_type' => get_class($this->party)
        ]);
        Networks::firstOrCreate([
            'owner_id' => $this->party->id,
            'owner_type' => get_class($this->party),
            'party_id' => messenger_profile()->id,
            'party_type' => get_class(messenger_profile())
        ]);
        $this->broadcastFriendAccept();
        return array("state" => true, "msg" => $this->party->name." is now in your friends list", "case" => 1);
    }

    private function broadcastFriendAdd()
    {
        try {
            broadcast(new FriendAdd([
                'msg' => messenger_profile()->name.' is requesting to be your friend. Please accept or deny their request',
            ],
                ['private-'.get_messenger_alias($this->party).'_notify_'.$this->party->id]
            ));
        } catch (Exception $e) {
            if(class_basename($e) === 'BroadcastException'){
                unset($e);
            }
            else{
                report($e);
            }
        }
    }

    private function broadcastFriendAccept()
    {
        try {
            broadcast(new FriendAccept([
                'msg' => messenger_profile()->name.' accepted your friend request',
            ],
                ['private-'.get_messenger_alias($this->party).'_notify_'.$this->party->id]
            ));
        } catch (Exception $e) {
            if(class_basename($e) === 'BroadcastException'){
                unset($e);
            }
            else{
                report($e);
            }
        }
    }

    private function broadcastFriendDenied()
    {
        try {
            broadcast(new FriendDenied([
                'msg' => messenger_profile()->name.' declined your friend request',
            ],
                ['private-'.get_messenger_alias($this->party).'_notify_'.$this->party->id]
            ));
        } catch (Exception $e) {
            if(class_basename($e) === 'BroadcastException'){
                unset($e);
            }
            else{
                report($e);
            }
        }
    }
}
