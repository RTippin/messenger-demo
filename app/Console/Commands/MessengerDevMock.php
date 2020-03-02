<?php

namespace App\Console\Commands;

use App\Services\Messenger\Faker\ThreadEvents;
use Illuminate\Console\Command;

/**
 * Class MessengerDevMock
 * @package App\Console\Commands
 */
class MessengerDevMock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messenger:dev-mock 
                                            {thread_id : ID of the thread you wish to mock action} 
                                            {--typing : Mock typing event} 
                                            {--read : mock read event}
                                            {--knock : mock knock event}
                                            {--purge-messages=0 : mock archiving messages from latest backwards using count}
                                            {--sub-delay=0 : run event with delay between broadcast}
                                            {--delay=0 : delay between loop}
                                            {--admins : Flag when running on group thread to only use admin participants for mock} 
                                            {--no-mobile : Flag to disable sending push notification over mobile}
                                            {--rounds=1 : Number of loops to run the events}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Messenger Mock Events Tool';

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
     * Locate thread from ID, perform events flagged
     * @param ThreadEvents $threadEvents
     * @return mixed
     */
    public function handle(ThreadEvents $threadEvents)
    {
        if(app()->isProduction()){
            $this->error('Not available on production');
            return true;
        }
        if($this->option('no-mobile')) config(['messenger.mobile_notify' => false]);
        $init = $threadEvents->event(
            $this->argument('thread_id'),
            $this->option('typing'),
            $this->option('read'),
            $this->option('knock'),
            $this->option('purge-messages'),
            $this->option('admins'),
            $this->option('sub-delay')
        );
        if($init['state']){
            $this->comment($init['msg']);
            $bar = $this->output->createProgressBar($this->option('rounds'));
            $bar->start();
            for($x = 1; $x <= $this->option('rounds'); $x++){
                $threadEvents->broadcast();
                $bar->advance();
                if($this->option('rounds') > $x) sleep($this->option('delay'));
            }
            $bar->finish();
            $this->line(' --DONE');
            $this->info(
                $threadEvents->complete()['msg']
            );
        }
        else{
            $this->error($init['msg']);
        }
        return true;
    }
}
