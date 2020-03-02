<?php

namespace App\Services\Messenger\Faker;
use App\Services\Messenger\ThreadService;
use Exception;

class PurgeThread extends MessengerDevService
{
    public function handle($thread_id, $messages, $read)
    {
        $load = $this->getThread($thread_id);
        if(!$load['state']){
            return $load;
        }

        if($messages) $this->purgeMessages();

        if($read) $this->purgeLastRead();

        if(ThreadService::IsGroup($this->thread)){
            return [
                'state' => true,
                'msg' => 'Purged resources from group '.$this->thread->name
            ];
        }
        return [
            'state' => true,
            'msg' => 'Purged resources from convo between '.$this->thread->participants[0]->owner->name.' and '.$this->thread->participants[1]->owner->name
        ];
    }

    private function purgeMessages()
    {
        try{
            $this->thread->messages()->delete();
            return true;
        }catch (Exception $e){
            report($e);
        }
        return false;
    }

    private function purgeLastRead()
    {
        try{
            $this->thread->participants()->update([
                'last_read' => null
            ]);
            return true;
        }catch (Exception $e){
            report($e);
        }
        return false;
    }
}