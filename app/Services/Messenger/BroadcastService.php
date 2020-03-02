<?php

namespace App\Services\Messenger;

use App\Events\AddedToThread;
use App\Events\CallEnded;
use App\Events\KickedFromThread;
use App\Events\MessagePurged;
use App\Events\NewMessage;
use App\Events\CallStarted;
use App\Events\KnockKnock;
use App\Models\Messages\Message;
use App\Models\Messages\Calls;
use App\Models\Messages\Participant;
use App\Services\PushNotificationService;
use App\Models\Messages\Thread;
use Exception;

class BroadcastService
{
    protected $thread,
        $channels = [],
        $devices;

    public function __construct(Thread $thread)
    {
        $this->thread = $thread;
    }

    public function broadcastChannels($all = false, $knok = false, $added = false, $collection = null)
    {
        if($added){
            $recipients = $collection;
        }
        else{
            $recipients = $all ? $this->thread->participants->fresh('owner.devices') : $this->thread->participants->where('owner_id', '!=', messenger_profile()->id);
        }
        $this->devices = collect([]);
        if(!empty($recipients)){
            foreach ($recipients as $recipient) {
                if($added){
                    if(config('messenger.mobile_notify') && $recipient['model']->devices){
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
                    if(config('messenger.mobile_notify') && $recipient->owner->devices){
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

    public function broadcastMessage(Message $message, $temp_id = null)
    {
        try {
            $data = MessengerRepo::MakeMessage($this->thread, $message, $temp_id);
            $data['thread_type'] = $this->thread->ttype;
            $data['thread_subject'] = $this->thread->name;

            foreach($this->channels as $channel){
                broadcast(new NewMessage($data, $channel));
            }

            if(config('messenger.mobile_notify')){
                $data['notification_type'] = 0;
                $notify = [
                    'title' => (ThreadService::IsGroup($this->thread) ? $this->thread->name : $message->owner->name),
                    'body'=> (ThreadService::IsGroup($this->thread) ? $message->owner->name.': ' : '').MessageService::MessageContentsFormat($this->thread, $message),
                    'sound' => 'default',
                    'data' => $data
                ];
                (new PushNotificationService())->sendPushNotify($this->devices, $notify);
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

            if(config('messenger.mobile_notify')){
                $data['notification_type'] = 1;
                $notify = [
                    'title' => null,
                    'body'=> null,
                    'sound' => 'default',
                    'data' => $data
                ];
                (new PushNotificationService())->sendPushNotify($this->devices, $notify);
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
        try {
            $data = [
                'thread_id' => $this->thread->id,
                'subject' => $this->thread->name,
                'name' => messenger_profile()->name
            ];

            foreach($this->channels as $channel){
                broadcast(new AddedToThread($data, $channel));
            }

            if(config('messenger.mobile_notify')){
                $data['notification_type'] = 5;
                $notify = [
                    'title' => $this->thread->name,
                    'body'=> messenger_profile()->name.' added you to the group',
                    'sound' => 'default',
                    'data' => $data
                ];
                (new PushNotificationService())->sendPushNotify($this->devices, $notify);
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
            broadcast(new KickedFromThread(['thread_id' => $this->thread->id], ['private-'.get_messenger_alias($participant->owner).'_notify_'.$participant->owner->id]));

            if(config('messenger.mobile_notify')){
                $data['notification_type'] = 6;
                $notify = [
                    'title' => null,
                    'body'=> null,
                    'sound' => 'default',
                    'data' => ['thread_id' => $this->thread->id]
                ];
                (new PushNotificationService())->sendPushNotify($participant->owner->devices, $notify);
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

    public function broadcastThreadPurged()
    {
        try {
            foreach($this->channels as $channel){
                broadcast(new KickedFromThread(['thread_id' => $this->thread->id], $channel));
            }

            if(config('messenger.mobile_notify')){
                $data['notification_type'] = 6;
                $notify = [
                    'title' => null,
                    'body'=> null,
                    'sound' => 'default',
                    'data' => ['thread_id' => $this->thread->id]
                ];
                (new PushNotificationService())->sendPushNotify($this->devices, $notify);
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
            $data = [
                'thread_id' => $this->thread->id,
                'thread_type' => $this->thread->ttype,
                'name' => messenger_profile()->name,
                'avatar' => messenger_profile()->avatar
            ];
            broadcast(new KnockKnock($data, ['private-'.get_messenger_alias($participant->owner).'_notify_'.$participant->owner->id]));

            if(config('messenger.mobile_notify')){
                $data['notification_type'] = 4;
                $notify = [
                    'title' => messenger_profile()->name.' is knocking!',
                    'body'=> null,
                    'sound' => 'default',
                    'data' => $data
                ];
                (new PushNotificationService())->sendPushNotify($participant->owner->devices, $notify);
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

    public function broadcastGroupKnok()
    {
        try {
            $data = [
                'thread_id' => $this->thread->id,
                'thread_type' => $this->thread->ttype,
                'name' => $this->thread->name,
                'avatar' => $this->thread->avatar
            ];

            foreach($this->channels as $channel){
                broadcast(new KnockKnock($data, $channel));
            }

            if(config('messenger.mobile_notify')){
                $data['notification_type'] = 4;
                $notify = [
                    'title' => $this->thread->name.' is knocking!',
                    'body'=> null,
                    'sound' => 'default',
                    'data' => $data
                ];
                (new PushNotificationService())->sendPushNotify($this->devices, $notify);
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

        try {
            $data = [
                'thread_id' => $this->thread->id,
                'thread_type' => $this->thread->ttype,
                'thread_name' => $this->thread->name,
                'call_id' => $call->id,
                'call_type' => $call->type,
                'room_id' => $call->room_id,
                'room_pin' => $call->room_pin,
                'room_secret' => $call->room_secret,
                'sender_name' => messenger_profile()->name,
                'avatar' => ThreadService::IsGroup($this->thread) ? $this->thread->avatar : messenger_profile()->avatar
            ];

            foreach($this->channels as $channel){
                broadcast(new CallStarted($data, $channel));
            }

            if(config('messenger.mobile_notify')){
                $data['notification_type'] = 2;
                $notify = [
                    'title' => (ThreadService::IsGroup($this->thread) ? $this->thread->name : messenger_profile()->name),
                    'body' => 'Incoming Call',
                    'sound' => 'default',
                    'data' => $data
                ];
                (new PushNotificationService())->sendPushNotify($this->devices, $notify, true);
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

        try {
            $data = [
                'thread_id' => $this->thread->id,
                'call_id' => $call->id
            ];

            foreach($this->channels as $channel){
                broadcast(new CallEnded($data, $channel));
            }

            if(config('messenger.mobile_notify')){
                $data['notification_type'] = 3;
                $notify = [
                    'title' => null,
                    'body'=> null,
                    'sound' => 'default',
                    'data' => $data
                ];
                (new PushNotificationService())->sendPushNotify($this->devices, $notify);
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