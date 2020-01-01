<?php

namespace App\Jobs;

use App\Services\Purge\MessagingPurge;
use App\Models\Messages\Thread;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PurgeArchivedThreads implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1;

    public function handle()
    {
        $threads = Thread::onlyTrashed()->get();
        foreach($threads as $thread){
            if($thread->deleted_at->addMonths(3) <= Carbon::now()){
                (new MessagingPurge($thread))->startDelete('thread');
            }
        }
        return;
    }
}