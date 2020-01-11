<?php

namespace App\Services\Messenger;

use App\Events\AddedToGroup;
use App\Events\CallEnded;
use App\Events\KickedFromGroup;
use App\Events\MessagePurged;
use App\Events\MessageSent;
use App\Events\NewCall;
use App\Events\SendKnok;
use App\Models\Messages\Message;
use App\Models\Messages\Calls;
use App\Models\Messages\Participant;
use App\Services\PushNotificationService;
use App\Models\Messages\Thread;
use Exception;

class BroadcastService
{
    protected $thread, $channels = [], $devices, $PushNotification;
    public function __construct(Thread $thread)
    {
        $this->thread = $thread;
        $this->PushNotification = new PushNotificationService();
    }

    public function broadcastChannels($all = false, $knok = false, $added = false, $collection = null)
    {
        if($added){
            $recipients = $collection;
        }
        else{
            $recipients = $all ? $this->thread->participants->fresh('owner.devices') : $this->thread->participants->where('owner_id', '!=', messenger_profile());
        }
        $this->devices = collect([]);
        if (!empty($recipients)) {
            foreach ($recipients as $recipient) {
                if($added){
                    if(config('messenger.mobile_notify') && $recipient['owner_type'] === "App\User" && $recipient['model']->devices){
                        foreach ($recipient['model']->devices as $device){
                            $this->devices->push($device);
                        }
                    }
                    array_push($this->channels, 'private-'.get_messenger_alias($recipient['model']).'_notify_'.$recipient['owner_id']);
                }
                else{
                    if($knok && !$recipient->owner->messenger->knoks){
                        continue;
                    }
                    if(config('messenger.mobile_notify') && $recipient->owner_type === "App\User" && $recipient->owner->devices){
                        foreach ($recipient->owner->devices as $device){
                            $this->devices->push($device);
                        }
                    }
                    array_push($this->channels, 'private-'.get_messenger_alias($recipient->owner).'_notify_'.$recipient->owner->id);
                }
            }
        }
        $this->channels = array_chunk($this->channels, 100);
        return $this;
    }

    public function broadcastMessage(Message $message)
    {
        try {
            $data = MessengerRepo::MakeMessage($this->thread, $message);
            $data['thread_type'] = $this->thread->ttype;
            $data['thread_subject'] = $this->thread->name;
            $notify = [
                'title' => (ThreadService::IsGroup($this->thread) ? $this->thread->name : $message->owner->name),
                'body'=> (ThreadService::IsGroup($this->thread) ? $message->owner->name.': ' : '').MessageService::MessageContentsFormat($this->thread, $message),
                'sound' => 'default',
                'data' => $data
            ];
            $this->PushNotification->sendPushNotify($this->devices, $notify);
            foreach($this->channels as $channel){
                broadcast(new MessageSent($data, $channel));
            }
        } catch (Exception $e) {
            if(class_basename($e) === 'BroadcastException'){
                unset($e);
            }
            else{
                report($e);
            }
        }
    }

    public function broadcastMessagePurged(Message $message)
    {
        try {
            $data = [
                'thread_id' => $this->thread->id,
                'message_id' => $message->id
            ];
            foreach($this->channels as $channel){
                broadcast(new MessagePurged($data, $channel));
            }
        } catch (Exception $e) {
            if(class_basename($e) === 'BroadcastException'){
                unset($e);
            }
            else{
                report($e);
            }
        }
    }

    public function broadcastAddedToThread()
    {
        $data = [
            'thread_id' => $this->thread->id,
            'subject' => $this->thread->name,
            'name' => messenger_profile()->name
        ];
        try {
            foreach($this->channels as $channel){
                broadcast(new AddedToGroup($data, $channel));
            }
        } catch (Exception $e) {
            if(class_basename($e) === 'BroadcastException'){
                unset($e);
            }
            else{
                report($e);
            }
        }
    }

    public function broadcastKicked(Participant $participant)
    {
        try {
            broadcast(new KickedFromGroup(['thread_id' => $this->thread->id], ['private-'.get_messenger_alias($participant->owner).'_notify_'.$participant->owner->id]));
        } catch (Exception $e) {
            if(class_basename($e) === 'BroadcastException'){
                unset($e);
            }
            else{
                report($e);
            }
        }
    }

    public function broadcastThreadPurged()
    {
        try {
            foreach($this->channels as $channel){
                broadcast(new KickedFromGroup(['thread_id' => $this->thread->id], $channel));
            }
        } catch (Exception $e) {
            if(class_basename($e) === 'BroadcastException'){
                unset($e);
            }
            else{
                report($e);
            }
        }
    }

    public function broadcastKnok(Participant $participant)
    {
        try {
            broadcast(new SendKnok([
                'thread_id' => $this->thread->id,
                'thread_type' => $this->thread->ttype,
                'name' => messenger_profile()->name,
                'avatar' => messenger_profile()->avatar
            ],
                ['private-'.get_messenger_alias($participant->owner).'_notify_'.$participant->owner->id]
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

    public function broadcastGroupKnok()
    {
        try {
            foreach($this->channels as $channel){
                broadcast(new SendKnok([
                    'thread_id' => $this->thread->id,
                    'thread_type' => $this->thread->ttype,
                    'name' => $this->thread->name,
                    'avatar' => $this->thread->avatar
                ], $channel));
            }
        } catch (Exception $e) {
            if(class_basename($e) === 'BroadcastException'){
                unset($e);
            }
            else{
                report($e);
            }
        }
    }

    public function broadcastCall(Calls $call)
    {
        $data = [
            'thread_id' => $this->thread->id,
            'thread_type' => $this->thread->ttype,
            'thread_name' => $this->thread->name,
            'call_id' => $call->id,
            'call_type' => $call->type,
            'sender_name' => messenger_profile()->name,
            'avatar' => ThreadService::IsGroup($this->thread) ? $this->thread->avatar : messenger_profile()->avatar,
        ];
        try {
            $this->PushNotification->sendPushNotify($this->devices, [
                'title' => (ThreadService::IsGroup($this->thread) ? $this->thread->name : messenger_profile()->name),
                'body' => 'Incoming Call',
                'sound' => 'default',
                'data' => $data
            ], true);
            foreach($this->channels as $channel){
                broadcast(new NewCall($data, $channel));
            }
        } catch (Exception $e) {
            if(class_basename($e) === 'BroadcastException'){
                unset($e);
            }
            else{
                report($e);
            }
        }
    }

    public function broadcastCallEnded(Calls $call)
    {
        $data = [
            'thread_id' => $this->thread->id,
            'call_id' => $call->id
        ];
        try {
            foreach($this->channels as $channel){
                broadcast(new CallEnded($data, $channel));
            }
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
