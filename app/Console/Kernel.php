<?php

namespace App\Console;

use App\Jobs\CallHealthChecks;
use App\Jobs\CheckThreadInvites;
use App\Jobs\PurgeArchivedMessages;
use App\Jobs\PurgeArchivedThreads;
use Artisan;
use Storage;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new CallHealthChecks())->everyMinute();
        $schedule->job(new CheckThreadInvites())->everyFifteenMinutes();
        $schedule->call(function (){
            Storage::deleteDirectory('public/messenger/');
            Storage::deleteDirectory('public/user/');
            Artisan::call('cache:clear');
            Artisan::call('migrate:fresh', [
                '--seed' => true,
            ]);
        })->weeklyOn(1, '3:00');
        //use the following to force delete threads/messages soft deleted and are older than 90 days
//        $schedule->job(new PurgeArchivedMessages())->twiceDaily(1, 13);
//        $schedule->job(new PurgeArchivedThreads())->twiceDaily(2, 14);

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
