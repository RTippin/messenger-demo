<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will run migrations, seeds, key generation, and documentation downloads.';

    /**
     * Store the responses file.
     */
    public function handle()
    {
        if (! $this->confirm('Proceed to Install? This will migrate fresh and wipe any existing database you have!')) {
            return;
        }

        $this->call('key:generate');

        $this->call('migrate:fresh', [
            '--seed' => true,
        ]);

        $this->call('download:docs');

        $this->info('Messenger demo installed!');
    }
}
