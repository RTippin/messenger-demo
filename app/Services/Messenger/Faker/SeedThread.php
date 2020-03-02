<?php

namespace App\Services\Messenger\Faker;

use App\Events\MockOnline;
use App\Events\MockRead;
use App\Events\MockTyping;
use App\Services\Messenger\MessageService;
use App\Services\Messenger\ParticipantService;
use App\Services\Messenger\ThreadService;
use Exception;

class SeedThread extends MessengerDevService
{
    protected $last_message,
        $participants,
        $admins,
        $events,
        $delay,
        $count,
        $used_participants;

    public function startSeed($thread_id, $count, $admins, $events, $delay)
    {
        $load = $this->getThread($thread_id);
        if(!$load['state']){
            return $load;
        }

        $this->admins = $admins;
        $this->delay = $delay;
        $this->count = $count;
        $this->events = $events;
        $this->participants = $this->thread->participants;
        $this->used_participants = collect([]);
        return [
            'state' => true,
            'msg' => 'Thread located, seeding...'
        ];
    }

    public function seed($more = true)
    {
        if(!$more) $this->delay = 0;
        if(ThreadService::IsGroup($this->thread)){
            $participants = $this->admins ? $this->thread->participants->where('admin', 1) : $this->thread->participants;
            $this->sendMessage($participants->random());
        }
        else{
            $this->sendMessage($this->participants->random());
        }
    }

    public function seedFinished()
    {
        sleep(1);
        if($this->events) $this->markRead();
        if(ThreadService::IsGroup($this->thread)){
            return [
                'msg' => 'Seeded '.$this->thread->name.' with '.$this->count.' messages using '.($this->admins ? 'only group admins' : 'all participants')
            ];
        }
        return [
            'msg' => 'Seeded convo between '.$this->participants[0]->owner->name.' and '.$this->participants[1]->owner->name.' with '.$this->count.' messages'
        ];
    }

    private function sendMessage($participant)
    {
        set_messenger_profile($participant->owner);
        $this->request->replace([
            'message' => $this->faker->realText(rand(10, 200), rand(1,4))
        ]);
        if($this->events){
            broadcast(new MockOnline($participant->owner, $this->thread));
            broadcast(new MockTyping($participant->owner, $this->thread));
        }
        sleep($this->delay);
        $this->last_message = MessageService::StoreNewMessage($this->request, $this->thread, $participant)['data'];
        $this->used_participants->push($participant);
    }

    private function markRead()
    {
        try{
            $this->used_participants->unique('owner_id')->each(function($participant){
                ParticipantService::MarkRead($participant);
                broadcast(new MockRead($participant->owner, $this->last_message));
            });
        }catch (Exception $e){
            report($e);
        }
    }
}