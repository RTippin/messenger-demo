<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RTippin\Messenger\Facades\Messenger;
use RTippin\Messenger\Models\Bot;
use RTippin\Messenger\Models\BotAction;
use RTippin\Messenger\Models\Thread;
use RTippin\MessengerBots\Bots\ChuckNorrisBot;
use RTippin\MessengerBots\Bots\CommandsBot;
use RTippin\MessengerBots\Bots\DadJokeBot;
use RTippin\MessengerBots\Bots\InsultBot;
use RTippin\MessengerBots\Bots\JokeBot;
use RTippin\MessengerBots\Bots\KanyeBot;
use RTippin\MessengerBots\Bots\RandomImageBot;
use RTippin\MessengerBots\Bots\ReactionBot;
use RTippin\MessengerBots\Bots\ReplyBot;
use RTippin\MessengerBots\Bots\RockPaperScissorsBot;
use RTippin\MessengerBots\Bots\RollBot;
use RTippin\MessengerBots\Bots\WeatherBot;
use RTippin\MessengerBots\Bots\YoMommaBot;

class BotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::where('email', '=', DatabaseSeeder::Admin['email'])->first();
        $group = Thread::group()->first();
        $bot = Bot::factory()->for($group)->owner($admin)->create([
            'name' => 'Messenger Bot',
            'cooldown' => 5,
        ]);
        Messenger::setProvider($bot);
        $react = [
            'reaction' => ':100:',
        ];
        $about = [
            'quote_original' => true,
            'replies' => [
                'Main package: https://github.com/RTippin/messenger',
                'Bots package: https://github.com/RTippin/messenger-bots',
                'Faker commands package: https://github.com/RTippin/messenger-faker',
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

        BotAction::factory()->for($bot)->owner($admin)->handler(ChuckNorrisBot::class)->match('exact')->triggers('!chuck')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(CommandsBot::class)->match('exact')->triggers('!commands')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(DadJokeBot::class)->match('exact')->triggers('!dadjoke')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(InsultBot::class)->match('exact')->triggers('!insult')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(JokeBot::class)->match('exact')->triggers('!joke')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(KanyeBot::class)->match('exact')->triggers('!kanye')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(RandomImageBot::class)->match('exact')->triggers('!image')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(ReactionBot::class)->match('contains')->triggers('100|nice')->payload(json_encode($react))->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(ReplyBot::class)->match('contains')->triggers('help|about|git')->payload(json_encode($about))->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(ReplyBot::class)->match('contains')->triggers('hi|hello|test|hallo')->payload(json_encode($hello))->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(RockPaperScissorsBot::class)->match('starts:with')->triggers('!rps')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(RollBot::class)->match('starts:with')->triggers('!r|!roll')->create();
//        BotAction::factory()->for($bot)->owner($admin)->handler(WeatherBot::class)->match('starts:with')->triggers('!w|!weather')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(YoMommaBot::class)->match('contains')->triggers('!yomomma|mom|mother')->create();
    }
}
