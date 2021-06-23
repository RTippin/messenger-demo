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
use RTippin\MessengerBots\Bots\KnockBot;
use RTippin\MessengerBots\Bots\RandomImageBot;
use RTippin\MessengerBots\Bots\ReactionBot;
use RTippin\MessengerBots\Bots\ReplyBot;
use RTippin\MessengerBots\Bots\RockPaperScissorsBot;
use RTippin\MessengerBots\Bots\RollBot;
use RTippin\MessengerBots\Bots\WeatherBot;
use RTippin\MessengerBots\Bots\WikiBot;
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
        ]);
        Messenger::setProvider($bot);
        $awesome = [
            'reaction' => ':100:',
        ];
        $thumbUp = [
            'reaction' => ':thumbsup:',
        ];
        $rofl = [
            'reaction' => ':rofl:',
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
        BotAction::factory()->for($bot)->owner($admin)->handler(CommandsBot::class)->match('exact:caseless')->triggers('!commands|!c')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(DadJokeBot::class)->match('contains:caseless')->triggers('!dadjoke|daddy|dad|father')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(InsultBot::class)->match('contains:any:caseless')->triggers('!insult|fuck|asshole|bitch')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(JokeBot::class)->match('exact:caseless')->triggers('!joke')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(KanyeBot::class)->match('exact:caseless')->triggers('!kanye')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(KnockBot::class)->match('contains:caseless')->triggers('!knock|knock|ding|dong|alert')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(RandomImageBot::class)->match('exact:caseless')->triggers('!image')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(ReactionBot::class)->match('contains:caseless')->triggers('100|nice|cool|wow|awesome')->payload(json_encode($awesome))->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(ReactionBot::class)->match('contains:caseless')->triggers('100|nice|cool|wow|awesome')->payload(json_encode($thumbUp))->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(ReactionBot::class)->match('contains:caseless')->triggers('lmao|lol|ha|lmfao|rofl')->payload(json_encode($rofl))->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(ReplyBot::class)->match('contains:caseless')->triggers('help|about|git|package|error')->payload(json_encode($about))->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(ReplyBot::class)->match('contains:caseless')->triggers('hi|hello|test|hallo')->payload(json_encode($hello))->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(RockPaperScissorsBot::class)->match('starts:with:caseless')->triggers('!rps')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(RollBot::class)->match('starts:with:caseless')->triggers('!r|!roll')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(WeatherBot::class)->match('starts:with:caseless')->triggers('!w|!weather')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(WikiBot::class)->match('starts:with:caseless')->triggers('!wiki')->create();
        BotAction::factory()->for($bot)->owner($admin)->handler(YoMommaBot::class)->match('contains:caseless')->triggers('!yomomma|mom|mother|mommy')->create();
    }
}
