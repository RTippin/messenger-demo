<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RTippin\Messenger\Models\Friend;
use Throwable;

class FriendsTableSeeder extends Seeder
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
        });
    }
}
