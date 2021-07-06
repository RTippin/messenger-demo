<?php

namespace App\Bots;

use RTippin\Messenger\Actions\Bots\BotActionHandler;
use Throwable;

class RecursionBot extends BotActionHandler
{
    /**
     * The bots settings.
     *
     * @return array
     */
    public static function getSettings(): array
    {
        return [
            'alias' => 'recursion',
            'description' => 'Recursion gag, showing off message replies.',
            'name' => 'Recursion!',
            'unique' => true,
            'match' => 'exact',
            'triggers' => ['!recursion'],
        ];
    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $first = $this->composer()->emitTyping()->message('!recursion !recursion', $this->message->id)->getMessage();

        $second = $this->composer()->message('!recursion !recursion !recursion', $first->id)->getMessage();

        $third = $this->composer()->message('!recursion !recursion !recursion !recursion', $second->id)->getMessage();

        $this->composer()->message('!recursion !recursion !recursion !recursion !recursion', $third->id);

        sleep(2);

        $this->composer()->emitTyping()->message('FATAL ERROR 500: max_execution_time exceeded');

        $this->composer()->message('Oh no! :open_mouth:');
    }
}
