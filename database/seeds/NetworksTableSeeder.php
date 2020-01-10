<?php

use App\Models\Networks\Networks;
use App\User;
use Illuminate\Database\Seeder;

class NetworksTableSeeder extends Seeder
{
    public function run()
    {
        $all = User::all();
        $admins = User::whereIn('email', ['admin@test.com', 'admin2@test.com', 'admin3@test.com'])->get();
        $admins->each(function ($user) use ($all){
            $all->where('id', '!=', $user->id)->each(function ($party) use($user){
                Networks::firstOrCreate([
                    'owner_id' => $user->id,
                    'owner_type' => 'App\User',
                    'party_id' => $party->id,
                    'party_type' => 'App\User'
                ]);
                Networks::firstOrCreate([
                    'owner_id' => $party->id,
                    'owner_type' => 'App\User',
                    'party_id' => $user->id,
                    'party_type' => 'App\User'
                ]);
            });
        });
    }
}
