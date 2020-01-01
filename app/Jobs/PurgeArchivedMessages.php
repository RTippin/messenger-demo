<?php

namespace App\Jobs;

use App\Models\Messages\Message;
use App\Services\Purge\MessagingPurge;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PurgeArchivedMessages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1;

    public function handle()
    {
        $messages = Message::onlyTrashed()->get();
        foreach($messages as $message){
            if($message->deleted_at->addMonths(3) <= Carbon::now()){
                (new MessagingPurge($message))->startDelete('message');
            }
        }
        return;
    }
}