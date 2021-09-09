<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DownloadDocsCommand extends Command
{
    /**
     * Git endpoints we download docs from.
     */
    const BaseUri = 'https://raw.githubusercontent.com/RTippin/messenger/1.x/';
    const BaseStorage = '/messenger-docs/';
    const Responses = 'docs/generated/responses.json';
    const Broadcast = 'docs/generated/broadcast.json';
    const README = 'README.md';
    const Installation = 'docs/Installation.md';
    const Configuration = 'docs/Configuration.md';
    const Commands = 'docs/Commands.md';
    const Broadcasting = 'docs/Broadcasting.md';
    const ChatBots = 'docs/ChatBots.md';
    const Calling = 'docs/Calling.md';
    const Composer = 'docs/Composer.md';
    const Helpers = 'docs/Helpers.md';
    const Events = 'docs/Events.md';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:docs';

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
        Storage::put(self::BaseStorage.'messenger-responses.json', file_get_contents(self::BaseUri.self::Responses));
        Storage::put(self::BaseStorage.'messenger-broadcast.json', file_get_contents(self::BaseUri.self::Broadcast));
        Storage::put(self::BaseStorage.'README.md', file_get_contents(self::BaseUri.self::README));
        Storage::put(self::BaseStorage.'Installation.md', file_get_contents(self::BaseUri.self::Installation));
        Storage::put(self::BaseStorage.'Configuration.md', file_get_contents(self::BaseUri.self::Configuration));
        Storage::put(self::BaseStorage.'Commands.md', file_get_contents(self::BaseUri.self::Commands));
        Storage::put(self::BaseStorage.'Broadcasting.md', file_get_contents(self::BaseUri.self::Broadcasting));
        Storage::put(self::BaseStorage.'ChatBots.md', file_get_contents(self::BaseUri.self::ChatBots));
        Storage::put(self::BaseStorage.'Calling.md', file_get_contents(self::BaseUri.self::Calling));
        Storage::put(self::BaseStorage.'Composer.md', file_get_contents(self::BaseUri.self::Composer));
        Storage::put(self::BaseStorage.'Helpers.md', file_get_contents(self::BaseUri.self::Helpers));
        Storage::put(self::BaseStorage.'Events.md', file_get_contents(self::BaseUri.self::Events));

        $this->info('All messenger documentation files have been downloaded and stored in "storage/app/messenger-docs/"');
    }
}
