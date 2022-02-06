<?php

namespace Database\Seeders;

use App\Bots\RecursionBot;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RTippin\Messenger\Actions\Bots\InstallPackagedBot;
use RTippin\Messenger\Facades\Messenger;
use RTippin\Messenger\MessengerBots;
use RTippin\Messenger\Models\Bot;
use RTippin\Messenger\Models\BotAction;
use RTippin\Messenger\Models\Thread;
use RTippin\MessengerBots\Bots\ReactionBombBot;
use RTippin\MessengerBots\Bots\ReplyBot;
use RTippin\MessengerBots\Packages\GamesPackage;
use RTippin\MessengerBots\Packages\JokesterPackage;
use RTippin\MessengerBots\Packages\NeoPackage;
use RTippin\MessengerFaker\Bots\FakerBot;
use Throwable;

class BotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function run(): void
    {
        DB::transaction(function () {
            $admin = User::where('email', '=', DatabaseSeeder::Admin['email'])->first();
            $group = Thread::group()->first();

            Messenger::setProvider($admin);

            $this->installMessengerBot($group, $admin);

            $this->installPackagedBots($group);
        });
    }

    /**
     * @param  Thread  $thread
     * @param  User  $admin
     * @return void
     *
     * @throws Throwable
     */
    private function installMessengerBot(Thread $thread, User $admin): void
    {
        $bot = Bot::factory()->for($thread)->owner($admin)->create([
            'name' => 'Messenger Bot',
        ]);

        foreach ($this->actions() as $action) {
            BotAction::factory()
                ->for($bot)
                ->owner($admin)
                ->handler($action[0])
                ->match($action[1])
                ->triggers($action[2])
                ->payload($action[3] ?? null)
                ->cooldown($action[4] ?? 0)
                ->create();
        }
    }

    /**
     * @param  Thread  $thread
     * @return void
     *
     * @throws Throwable
     */
    private function installPackagedBots(Thread $thread): void
    {
        app(InstallPackagedBot::class)
            ->withoutEvents()
            ->execute($thread, GamesPackage::getDTO());

        app(InstallPackagedBot::class)
            ->withoutEvents()
            ->execute($thread, JokesterPackage::getDTO());

        app(InstallPackagedBot::class)
            ->withoutEvents()
            ->execute($thread, NeoPackage::getDTO());
    }

    /**
     * @return array
     */
    private function actions(): array
    {
        $about = [
            'quote_original' => true,
            'replies' => [
                'Core package: https://github.com/RTippin/messenger',
                'Ready-made Bots: https://github.com/RTippin/messenger-bots',
                'Faker commands package: https://github.com/RTippin/messenger-faker',
                'UI package: https://github.com/RTippin/messenger-ui',
                'Demo app: https://github.com/RTippin/messenger-demo',
                ':robot::robot::robot::robot:',
            ],
        ];
        $hello = [
            'quote_original' => false,
            'replies' => [
                'Why hello there!',
            ],
        ];
        $bomb = [
            'reactions' => ['🤖', '👋'],
        ];

        return [
            [ReplyBot::class, MessengerBots::MATCH_CONTAINS_CASELESS, 'help|git|package|error', $about, 300],
            [ReplyBot::class, MessengerBots::MATCH_CONTAINS_CASELESS, 'hi|hello|test|testing|hallo', $hello, 45],
            [RecursionBot::class, MessengerBots::MATCH_EXACT_CASELESS, '!recursion'],
            [FakerBot::class, MessengerBots::MATCH_STARTS_WITH_CASELESS, '!faker'],
            [ReactionBombBot::class, MessengerBots::MATCH_CONTAINS_CASELESS, 'hi|hello|test|testing|hallo', $bomb, 45],
        ];
    }
}
