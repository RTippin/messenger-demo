<?php

namespace App\Console\Commands;

use App\Jobs\CallHealthChecks;
use App\Jobs\CheckThreadInvites;
use App\Services\Messenger\CallService;
use App\Services\Messenger\InvitationService;
use Illuminate\Console\Command;

class MessengerCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messenger:check
                                    {--calls : Trigger call participant activity checker if active calls exist}
                                    {--invites : Run through thread invites to check validity}
                                    {--now : Perform requested checks now instead of using queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform the flagged health checks for messenger';

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
        if($this->option('calls') && CallService::ActiveCallsExist()){
            $this->option('now') ? CallService::CallParticipantChecks(true) : CallHealthChecks::dispatch();
        }
        if($this->option('invites')){
            $this->option('now') ? InvitationService::ValidateAllInvites() : CheckThreadInvites::dispatch();
        }
        return;
    }
}
