<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * The main admin account we create.
     */
    const Admin = [
        'name' => 'John Doe',
        'email' => 'admin@example.net',
    ];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            ThreadsTableSeeder::class,
            MessagesTableSeeder::class,
            FriendsTableSeeder::class,
            BotSeeder::class,
        ]);
    }
}
