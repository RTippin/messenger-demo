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
        $password = 'Messenger1!';
        $admins = [
            ['admin@test.com', 'Richard', 'Tippin'],
            ['admin2@test.com', 'Test', 'McTest'],
            ['admin3@test.com', 'John', 'Admin']
        ];
        foreach ($admins as $admin){
            $user = User::create([
                'email' => $admin[0],
                'first' => $admin[1],
                'last' => $admin[2],
                'active' => 1,
                'password' => Hash::make($password)
            ]);
            $user->messenger()->create([
                'owner_id' => $user->id,
                'owner_type' => 'App\User',
                'slug' => $user->last.'-'.Str::random(4).'-'.Carbon::now()->timestamp,
            ]);
        }
        factory(App\User::class, 15)->create()->each(function ($user){
            $user->messenger()->create([
                'owner_id' => $user->id,
                'owner_type' => 'App\User',
                'slug' => $user->last.'-'.Str::random(4).'-'.Carbon::now()->timestamp
            ]);
        });
    }
}
