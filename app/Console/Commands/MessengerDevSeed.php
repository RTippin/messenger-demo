<?php

namespace App\Console\Commands;

use App\Services\Messenger\Faker\SeedThread;
use Illuminate\Console\Command;

class MessengerDevSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messenger:dev-seed 
                                            {thread_id : ID of the thread you wish to seed with messages} 
                                            {--count=5 : The number of messages you want to be seeded} 
                                            {--admins : Flag when running on group thread to only use admin participants to seed} 
                                            {--events : Flag to enable mocking presence channel events and marking read}
                                            {--no-mobile : Flag to disable sending push notification over app when events is enabled}
                                            {--delay=1 : Delay in seconds between each message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Messenger Seeder Tool';

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
     * Locate thread from ID, seed with <COUNT> messages using all or
     * admin participants with <DELAY> between each message
     * @param SeedThread $seedThread
     * @return mixed
     */
    public function handle(SeedThread $seedThread)
    {
        if(app()->isProduction()){
            $this->error('Not available on production');
            return true;
        }
        if($this->option('no-mobile')) config(['messenger.mobile_notify' => false]);
        $init = $seedThread->startSeed(
            $this->argument('thread_id'),
            $this->option('count'),
            $this->option('admins'),
            $this->option('events'),
            $this->option('delay')
        );
        if($init['state']){
            $this->comment($init['msg']);
            $bar = $this->output->createProgressBar($this->option('count'));
            $bar->start();
            for($x = 1; $x <= $this->option('count'); $x++){
                $seedThread->seed($this->option('count') > $x);
                $bar->advance();
            }
            $bar->finish();
            $this->line(' --DONE');
            $this->info(
                $seedThread->seedFinished()['msg']
            );
        }
        else{
            $this->error($init['msg']);
        }
        return true;
    }
}
