<?php

namespace App\Console\Commands;

use App\Jobs\PurgeArchivedMessages;
use App\Jobs\PurgeArchivedThreads;
use App\Services\Messenger\MessageService;
use App\Services\Messenger\ThreadService;
use Illuminate\Console\Command;

class MessengerPurge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messenger:purge
                                    {--threads : Run check on threads}
                                    {--messages : Run check on messages}
                                    {--days=90 : Purge resource soft deleted X days or older}
                                    {--now : Perform requested actions now instead of using queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge messenger soft deleted resources older than specified days using flagged resource';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if($this->option('threads')){
            $this->option('now') ? ThreadService::PurgeArchivedThreads($this->option('days')) : PurgeArchivedThreads::dispatch($this->option('days'));
        }

        if($this->option('messages')){
            $this->option('now') ? MessageService::PurgeArchivedMessages($this->option('days')) : PurgeArchivedMessages::dispatch($this->option('days'));
        }

        return;
    }
}
