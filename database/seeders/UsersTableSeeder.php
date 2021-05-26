<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Main admin account
        User::factory()->admin()->create([
            'name' => DatabaseSeeder::Admin['name'],
            'email' => DatabaseSeeder::Admin['email'],
        ]);

        // Random accounts
        User::factory()->count(15)->create();
    }
}
