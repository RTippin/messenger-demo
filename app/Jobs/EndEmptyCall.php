<?php

namespace App\Jobs;


use App\Models\Messages\Calls;
use App\Services\Messenger\CallService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class EndEmptyCall implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1;
    protected $call;

    public function __construct($call_id)
    {
        $this->call = Calls::with(['participants', 'thread.participants'])->find($call_id);
    }

    public function handle()
    {
        if($this->call
            && $this->call->active
            && !CallService::CallActiveCount($this->call)
        ){
            CallService::PerformCallShutdown($this->call->thread, $this->call);
        }
        return;
    }
}