<?php

namespace App\Jobs;

use App\Services\Messenger\MessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PurgeArchivedMessages implements ShouldQueue
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
        MessageService::PurgeArchivedMessages($this->days);
        return;
    }
}