<?php

namespace App\Services\Social;
use App\Models\Networks\Networks;
use App\Models\Networks\PendingNetworks;
use App\Notifications\NetworksAccept;
use App\Notifications\NetworksAdd;
use App\Services\Service;
use App\Models\User\UserInfo;
use Illuminate\Http\Request;
use Exception;

class NetworksService extends Service
{
    protected $party;
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function handleActions()
    {
        $this->partyModel();
        if(!$this->party || $this->party->id === $this->modelType()->id){
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

    public static function MakeNetworkRequest($model)
    {
        $friends = collect([]);
        try{
            $model->pendingReceivedNetworks->reverse()->each(function ($friend) use($friends){
                $friends->push([
                    'id' => $friend->id,
                    'owner_id' => $friend->sender->id,
                    'name' => $friend->sender->name,
                    'slug' => $friend->sender->slug(),
                    'avatar' => $friend->sender->avatar,
                    'type' => strtolower(class_basename($friend->sender)),
                    'time_ago' => $friend->created_at->diffForHumans(),
                    'created_at' => $friend->created_at->toDateTimeString()
                ]);
            });
        }catch (Exception $e){
            report($e);
        }
        return $friends;
    }

    private function exist()
    {
        return $this->modelType()->networkStatus($this->party);
    }

    private function partyModel()
    {
        $slug = UserInfo::where('slug', $this->request->input('slug'))->first();
        $this->party = ($slug ? $slug->user : null);
    }

    private function addToNetwork()
    {
        if($this->exist() !== 0){
            return array("state" => false, "error" => "Unable to proceed");
        }
        $data = PendingNetworks::firstOrCreate([
            'sender_id' => $this->modelType()->id,
            'sender_type' => get_class($this->modelType()),
            'recipient_id' => $this->party->id,
            'recipient_type' => get_class($this->party)
        ]);
        $this->broadcastNetworkActivity(["data" => $data, "action" => true, "type" => false]);
        return array("state" => true, "msg" => "You sent a friend request to ".$this->party->name.". They must approve it before you become connected.", "case" => 2);
    }

    private function cancelNetworkRequest()
    {
        if($this->exist() !== 2){
            return array("state" => false, "error" => "Unable to proceed");
        }
        $this->modelType()->pendingSentNetworks()->where('recipient_id', $this->party->id)->where('recipient_type', get_class($this->party))->delete();
        $this->party->notifications()->where('type', 'App\Notifications\NetworksAdd')->where('data', 'LIKE', '%'.$this->modelType()->id.'%')->delete();
        return array("state" => true, "msg" => "Removed friend request to ".$this->party->name, "case" => 0);
    }

    private function acceptNetworkRequest()
    {
        if($this->exist() !== 3){
            return array("state" => false, "error" => "Unable to proceed");
        }
        $this->party->pendingSentNetworks()->where('recipient_id', $this->modelType()->id)->where('recipient_type', get_class($this->modelType()))->delete();
        $notification = $this->modelType()->notifications()->where('type', 'App\Notifications\NetworksAdd')->where('data', 'LIKE', '%'.$this->party->id.'%')->latest()->first();
        if($notification){
            $new_data = collect([
                "action" => false,
                "owner_id" => $notification->data['owner_id'],
                "owner_type" => $notification->data['owner_type']
            ]);
            $notification->data = $new_data;
            $notification->save();
        }
        return $this->joinNetworks(true);
    }

    private function denyNetworkRequest()
    {
        if($this->exist() !== 3){
            return array("state" => false, "error" => "Unable to proceed");
        }
        $this->party->pendingSentNetworks()->where('recipient_id', $this->modelType()->id)->where('recipient_type', get_class($this->modelType()))->delete();
        $this->modelType()->notifications()->where('type', 'App\Notifications\NetworksAdd')->where('data', 'LIKE', '%'.$this->party->id.'%')->delete();
        return array("state" => true, "msg" => "You denied the friend request from ".$this->party->name, "case" => 0);
    }

    private function removeNetworks()
    {
        if($this->exist() !== 1){
            return array("state" => false, "error" => "Unable to proceed");
        }
        $this->modelType()->networks()->where('party_id', $this->party->id)->where('party_type', get_class($this->party))->delete();
        $this->party->networks()->where('party_id', $this->modelType()->id)->where('party_type', get_class($this->modelType()))->delete();
        return array("state" => true, "msg" => "You have removed ".$this->party->name." from your friends", "case" => 0);
    }

    private function joinNetworks($type)
    {
        Networks::firstOrCreate([
            'owner_id' => $this->modelType()->id,
            'owner_type' => get_class($this->modelType()),
            'party_id' => $this->party->id,
            'party_type' => get_class($this->party)
        ]);
        $data = Networks::firstOrCreate([
            'owner_id' => $this->party->id,
            'owner_type' => get_class($this->party),
            'party_id' => $this->modelType()->id,
            'party_type' => get_class($this->modelType())
        ]);
        $this->broadcastNetworkActivity(["data" => $data, "action" => false, "type" => $type]);
        return array("state" => true, "msg" => $this->party->name." is now in your friends list", "case" => 1);
    }

    private function broadcastNetworkActivity(array $data)
    {
        if($data['type']){
            $this->party->notify(new NetworksAccept($data['data']));
            return;
        }
        $this->party->notify(new NetworksAdd($data['data'], $data['action']));
    }
}
