<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RTippin\Messenger\Models\Friend;

class FriendsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $users = User::all();

        // Make ALL users friends with one another
        foreach ($users as $user) {
            $others = $users->where('email', '!=', $user->email)->all();

            foreach ($others as $other) {
                if (Friend::forProvider($user)->forProvider($other, 'party')->doesntExist()) {
                    Friend::factory()->providers($user, $other)->create();
                    Friend::factory()->providers($other, $user)->create();
                }
            }
        }
    }
}
