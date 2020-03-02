<?php

namespace App\Jobs;

use App\Services\Messenger\ThreadService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PurgeArchivedThreads implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $days;

    public function __construct($days)
    {
        $this->days = $days;
    }

    public function handle()
    {
        ThreadService::PurgeArchivedThreads($this->days);
        return;
    }
}