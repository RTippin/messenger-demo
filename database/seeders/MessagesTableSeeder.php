<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RTippin\Messenger\Models\Message;
use RTippin\Messenger\Models\Thread;

class MessagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Thread::with('participants.owner')->get()->each(function(Thread $thread) {
            for ($x = 0; $x < rand(15, 30); $x++) {
                Message::factory()
                    ->for($thread)
                    ->owner($thread->participants->random()->owner)
                    ->create();
            }
        });
    }
}
