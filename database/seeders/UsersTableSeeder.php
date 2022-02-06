<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Throwable;

class UsersTableSeeder extends Seeder
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
            // Main admin account
            User::factory()->admin()->create([
                'name' => DatabaseSeeder::Admin['name'],
                'email' => DatabaseSeeder::Admin['email'],
            ]);

            // Random accounts
            User::factory()->count(15)->create();
        });
    }
}
