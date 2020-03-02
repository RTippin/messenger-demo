<?php

namespace App\Services\Messenger\Faker;

use App\Events\MockOnline;
use App\Events\MockRead;
use App\Events\MockTyping;
use App\Services\Messenger\BroadcastService;
use App\Services\Messenger\ParticipantService;
use App\Services\Messenger\ThreadService;
use Exception;

class ThreadEvents extends MessengerDevService
{
    protected $last_message,
        $participants,
        $typing,
        $read,
        $knock,
        $purge_messages,
        $delay,
        $purged = false;

    public function event($thread_id,
                          $typing,
                          $read,
                          $knock,
                          $purge_messages,
                          $admins,
                          $delay
    )
    {
        $load = $this->getThread($thread_id);
        if(!$load['state']) {
            return $load;
        }
        $this->participants = $this->thread->participants;
        $this->typing = $typing;
        $this->read = $read;
        $this->delay = $delay;
        $this->knock = $knock;
        $this->purge_messages = $purge_messages;
        $this->last_message = $this->thread->latestMessage();
        if(!$this->last_message){
            return [
                'state' => false,
                'msg' => 'No messages found. Must have messages to start events'
            ];
        }
        if(ThreadService::IsGroup($this->thread) && $admins) $this->participants = $this->thread->participants->where('admin', 1);
        return [
            'state' => true,
            'msg' => 'Running events'
        ];
    }

    public function broadcast()
    {
        if($this->read) $this->read();
        if($this->typing) $this->typing();
        if($this->knock) $this->knock();
        if($this->purge_messages > 0) $this->purgeMessages();
    }

    public function complete()
    {
        if(ThreadService::IsGroup($this->thread)){
            return [
                'msg' => 'Events done for '.$this->thread->name
            ];
        }
        return [
            'msg' => 'Events done between '.$this->participants[0]->owner->name.' and '.$this->participants[1]->owner->name
        ];
    }

    private function read()
    {
        try{
            $this->participants->each(function($participant){
                ParticipantService::MarkRead($participant);
                broadcast(new MockRead($participant->owner, $this->last_message));
                sleep($this->delay);
            });
        }catch (Exception $e){
            report($e);
        }

    }

    private function typing()
    {
        try{
            $this->participants->each(function($participant){
                broadcast(new MockOnline($participant->owner, $this->thread));
                broadcast(new MockTyping($participant->owner, $this->thread));
                sleep($this->delay);
            });
        }catch (Exception $e){
            report($e);
        }
    }

    private function knock()
    {
        if(ThreadService::IsPrivate($this->thread)){
            set_messenger_profile($this->participants[0]->owner);
            ThreadService::SendKnock($this->thread, $this->participants[0], true);
            set_messenger_profile($this->participants[1]->owner);
            ThreadService::SendKnock($this->thread, $this->participants[1], true);
        }
        else{
            ThreadService::SendKnock($this->thread, $this->participants[0], true);
        }
    }

    private function purgeMessages()
    {
        if($this->purged) return;
        try{
            $x = 1;
            $messages = $this->thread->messages()->latest()->get();
            foreach($messages as $message){
                if($this->purge_messages < $x) break;
                (new BroadcastService($this->thread))->broadcastChannels(true)->broadcastMessagePurged($message);
                $x++;
                sleep($this->delay);
            }
            $this->purged = true;
        }catch (Exception $e){
            report($e);
        }
    }

}