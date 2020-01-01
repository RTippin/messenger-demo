<?php

namespace App\Jobs;


use App\Models\Messages\Calls;
use App\Services\Messenger\CallService;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class EndEmptyCall implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1;
    protected $call, $request;

    public function __construct($call)
    {
        $this->call = Calls::with(['participants', 'thread'])->find($call);
        $this->request = Request::capture();
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