<?php

namespace App\Console;

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
        $schedule->command('messenger:calls:check-activity')
            ->everyMinute();

        $schedule->command('messenger:invites:check-valid')
            ->everyFifteenMinutes();

        $schedule->command('messenger:purge:threads')
            ->dailyAt('1:00');

        $schedule->command('messenger:purge:messages')
            ->dailyAt('2:00');

        $schedule->command('messenger:purge:images')
            ->dailyAt('3:00');

        $schedule->command('messenger:purge:documents')
            ->dailyAt('4:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
