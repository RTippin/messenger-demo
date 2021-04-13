<?php

namespace Database\Seeders;

use App\Models\Company;
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
        $admin = User::where('email', '=', 'admin@example.net')->first();
        User::where('admin', '=', false)->get()->each(function(User $user) use ($admin) {
            Friend::factory()->providers($admin, $user)->create();
            Friend::factory()->providers($user, $admin)->create();
        });
        Company::all()->each(function(Company $company) use ($admin) {
            Friend::factory()->providers($admin, $company)->create();
            Friend::factory()->providers($company, $admin)->create();
        });
    }
}
