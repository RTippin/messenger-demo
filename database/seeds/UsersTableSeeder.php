<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\User::class, 10)->create()->each(function ($user){
            factory(App\Models\User\UserInfo::class)->create([
                'user_id' => $user->id,
                'slug' => $user->lastName.'-'.Str::random(4).'-'.Carbon::now()->timestamp
            ]);
            $user->messengerSettings()->create([
                'owner_id' => $user->id,
                'owner_type' => 'App\User'
            ]);
        });
    }
}
