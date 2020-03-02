<?php

namespace App\Console\Commands;


use App\Services\Messenger\Faker\PurgeThread;
use Illuminate\Console\Command;

class MessengerDevPurge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messenger:dev-purge 
                                            {thread_id : ID of the thread you wish to purge resource}
                                            {--messages : Use message resource}
                                            {--read : Use Participants last read resource}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Messenger Purge Tool';

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
     * Locate <thread_id> and purge all messages or last_read timestamps
     * @param PurgeThread $purgeThread
     * @return mixed
     */
    public function handle(PurgeThread $purgeThread)
    {
        if(app()->isProduction()){
            $this->error('Not available on production');
            return true;
        }
        $action = $purgeThread->handle(
            $this->argument('thread_id'),
            $this->option('messages'),
            $this->option('read')
        );
        if($action['state']){
            $this->info($action['msg']);
        }
        else{
            $this->error($action['msg']);
        }
        return true;
    }
}
