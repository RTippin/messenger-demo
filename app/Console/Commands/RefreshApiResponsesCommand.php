<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshApiResponsesCommand extends Command
{
    /**
     * Git endpoint where the most recent generated responses are stored.
     */
    const GIT_ENDPOINT = 'https://raw.githubusercontent.com/RTippin/messenger/1.x/docs/generated/responses.json';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messenger:get:api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download and store a fresh copy of all API responses generated from the messenger core.';

    /**
     * Store the responses file.
     */
    public function handle()
    {
        file_put_contents(storage_path('messenger-responses.json'), file_get_contents(self::GIT_ENDPOINT));

        $this->info("messenger-responses.json stored in storage!");
    }
}
