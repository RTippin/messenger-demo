<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DownloadDocumentation extends Command
{
    /**
     * Git endpoints we download docs from.
     */
    const Responses = 'https://raw.githubusercontent.com/RTippin/messenger/1.x/docs/generated/responses.json';
    const README = 'https://raw.githubusercontent.com/RTippin/messenger/1.x/README.md';
    const Installation = 'https://raw.githubusercontent.com/RTippin/messenger/1.x/docs/Installation.md';
    const Configuration = 'https://raw.githubusercontent.com/RTippin/messenger/1.x/docs/Configuration.md';
    const Commands = 'https://raw.githubusercontent.com/RTippin/messenger/1.x/docs/Commands.md';
    const Broadcasting = 'https://raw.githubusercontent.com/RTippin/messenger/1.x/docs/Broadcasting.md';
    const ChatBots = 'https://raw.githubusercontent.com/RTippin/messenger/1.x/docs/ChatBots.md';
    const Calling = 'https://raw.githubusercontent.com/RTippin/messenger/1.x/docs/Calling.md';
    const Composer = 'https://raw.githubusercontent.com/RTippin/messenger/1.x/docs/Composer.md';
    const Helpers = 'https://raw.githubusercontent.com/RTippin/messenger/1.x/docs/Helpers.md';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messenger:docs:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download and store the documentation we need from the source git repository.';

    /**
     * Store the responses file.
     */
    public function handle()
    {
        Storage::put('/messenger-docs/messenger-responses.json', file_get_contents(self::Responses));
        Storage::put('/messenger-docs/README.md', file_get_contents(self::README));
        Storage::put('/messenger-docs/Installation.md', file_get_contents(self::Installation));
        Storage::put('/messenger-docs/Configuration.md', file_get_contents(self::Configuration));
        Storage::put('/messenger-docs/Commands.md', file_get_contents(self::Commands));
        Storage::put('/messenger-docs/Broadcasting.md', file_get_contents(self::Broadcasting));
        Storage::put('/messenger-docs/ChatBots.md', file_get_contents(self::ChatBots));
        Storage::put('/messenger-docs/Calling.md', file_get_contents(self::Calling));
        Storage::put('/messenger-docs/Composer.md', file_get_contents(self::Composer));
        Storage::put('/messenger-docs/Helpers.md', file_get_contents(self::Helpers));

        $this->info('All messenger documentation files have been downloaded and stored in "storage/app/messenger-docs/"');
    }
}
