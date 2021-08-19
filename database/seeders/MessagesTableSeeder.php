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
        // Seed ALL threads with messages
        Thread::with('participants.owner')->get()->each(function (Thread $thread) {
            for ($x = 0; $x < rand(5, 20); $x++) {
                Message::factory()
                    ->for($thread)
                    ->owner($thread->participants->random()->owner)
                    ->create();
            }
        });
    }
}
