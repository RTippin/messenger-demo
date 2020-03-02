<?php

namespace App\Services\Messenger\Faker;
use App\Services\Messenger\ThreadService;
use Faker\Generator as Faker;
use Illuminate\Http\Request;

class MessengerDevService
{
    protected $request,
        $faker,
        $thread;

    public function __construct(Request $request, Faker $faker)
    {
        $this->request = $request;
        $this->faker = $faker;

    }

    protected function getThread($thread_id)
    {
        $this->thread = ThreadService::LocateThread($thread_id, ['participants.owner.devices','participants.owner.messenger']);
        if(!$this->thread){
            return [
                'state' => false,
                'msg' => 'Could not locate a thread with ID: '.$thread_id
            ];
        }
        return [
            'state' => true
        ];
    }
}