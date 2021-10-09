<?php

namespace Database\Seeders;

use App\Bots\RecursionBot;
use App\Models\User;
use Illuminate\Database\Seeder;
use RTippin\Messenger\Models\Bot;
use RTippin\Messenger\Models\BotAction;
use RTippin\Messenger\Models\Thread;
use RTippin\MessengerBots\Bots\ChuckNorrisBot;
use RTippin\MessengerBots\Bots\CoinTossBot;
use RTippin\MessengerBots\Bots\CommandsBot;
use RTippin\MessengerBots\Bots\DadJokeBot;
use RTippin\MessengerBots\Bots\GiphyBot;
use RTippin\MessengerBots\Bots\InsultBot;
use RTippin\MessengerBots\Bots\InviteBot;
use RTippin\MessengerBots\Bots\JokeBot;
use RTippin\MessengerBots\Bots\KanyeBot;
use RTippin\MessengerBots\Bots\KnockBot;
use RTippin\MessengerBots\Bots\LocationBot;
use RTippin\MessengerBots\Bots\QuotableBot;
use RTippin\MessengerBots\Bots\RandomImageBot;
use RTippin\MessengerBots\Bots\ReactionBot;
use RTippin\MessengerBots\Bots\ReplyBot;
use RTippin\MessengerBots\Bots\RockPaperScissorsBot;
use RTippin\MessengerBots\Bots\RollBot;
use RTippin\MessengerBots\Bots\WeatherBot;
use RTippin\MessengerBots\Bots\WikiBot;
use RTippin\MessengerBots\Bots\YoMommaBot;
use RTippin\MessengerBots\Bots\YoutubeBot;
use RTippin\MessengerFaker\Bots\FakerBot;

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

        foreach ($this->actions() as $action) {
            BotAction::factory()
                ->for($bot)
                ->owner($admin)
                ->handler($action[0])
                ->match($action[1])
                ->triggers($action[2])
                ->payload($action[3] ?? null)
                ->create();
        }
    }

    /**
     * @return array
     */
    private function actions(): array
    {
        $awesome = ['reaction' => ':100:'];
        $thumbUp = ['reaction' => ':thumbsup:'];
        $rofl = ['reaction' => ':rofl:'];
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

        return [
            [ChuckNorrisBot::class, 'exact:caseless', '!chuck'],
            [CommandsBot::class, 'exact:caseless', '!commands|!c'],
            [DadJokeBot::class, 'contains:caseless', '!dadjoke|daddy|dad'],
            [GiphyBot::class, 'starts:with:caseless', '!gif|!giphy'],
            [InsultBot::class, 'contains:any:caseless', '!insult|fuck|asshole|bitch'],
            [JokeBot::class, 'exact:caseless', '!joke'],
            [KanyeBot::class, 'exact:caseless', '!kanye'],
            [KnockBot::class, 'contains:caseless', '!knock|knock|ding|dong|alert'],
            [RandomImageBot::class, 'exact:caseless', '!image'],
            [RockPaperScissorsBot::class, 'starts:with:caseless', '!rps'],
            [RollBot::class, 'starts:with:caseless', '!r|!roll'],
            [WeatherBot::class, 'starts:with:caseless', '!w|!weather'],
            [WikiBot::class, 'starts:with:caseless', '!wiki'],
            [YoutubeBot::class, 'starts:with:caseless', '!youtube'],
            [YoMommaBot::class, 'contains:caseless', '!yomomma|mom|mother|mommy'],
            [LocationBot::class, 'exact:caseless', '!location|!findMe|!whereAmI'],
            [ReactionBot::class, 'contains:caseless', '100|nice|cool|wow|awesome', $awesome],
            [ReactionBot::class, 'contains:caseless', '100|nice|cool|wow|awesome', $thumbUp],
            [ReactionBot::class, 'contains:caseless', 'lmao|lol|ha|lmfao|rofl', $rofl],
            [ReplyBot::class, 'contains:caseless', 'help|about|git|package|error', $about],
            [ReplyBot::class, 'contains:caseless', 'hi|hello|test|testing|hallo', $hello],
            [RecursionBot::class, 'exact:caseless', '!recursion'],
            [QuotableBot::class, 'exact:caseless', '!quote|!inspire|!quotable'],
            [CoinTossBot::class, 'starts:with:caseless', '!toss|!headsOrTails|!coinToss'],
            [FakerBot::class, 'starts:with:caseless', '!faker'],
            [InviteBot::class, 'exact:caseless', '!invite|!inv'],
        ];
    }
}
