<?php

use App\Models\Messages\Participant;
use App\User;
use Illuminate\Database\Seeder;
use App\Models\Messages\Thread;

class ThreadsTableSeeder extends Seeder
{
    protected $admins = ['admin@test.com', 'admin2@test.com', 'admin3@test.com'];
    public function makeAdminPrivates($users, $owner)
    {
        foreach($users as $user){
            $thread = Thread::create();
            $owners = collect([$owner->id, $user->id]);
            Participant::create([
                'thread_id' => $thread->id,
                'owner_id' => $owner->id,
                'owner_type' => 'App\User'
            ]);
            Participant::create([
                'thread_id' => $thread->id,
                'owner_id' => $user->id,
                'owner_type' => 'App\User'
            ]);
            $num = rand(5,20);
            for($x = 0; $x <= $num; $x++){
                factory(App\Models\Messages\Message::class)->create([
                    'thread_id' => $thread->id,
                    'owner_id' => $owners->random(),
                    'owner_type' => 'App\User'
                ]);
                usleep(100000);
            }
        }
    }

    public function makeAdminGroupParticipant($admins, $thread)
    {
        foreach($admins as $admin){
            Participant::create([
                'thread_id' => $thread->id,
                'owner_id' => $admin->id,
                'owner_type' => 'App\User',
                'admin' => 1
            ]);
        }
    }

    public function run()
    {
        $all = User::all();
        $super = $all->where('email', 'admin@test.com')->first();
        $super2 = $all->where('email', 'admin2@test.com')->first();
        $this->makeAdminPrivates($all->whereNotIn('email', ['admin@test.com', 'admin2@test.com']), $super);
        $this->makeAdminPrivates($all->whereNotIn('email', ['admin@test.com', 'admin2@test.com']), $super2);
        //make admin group thread
        $thread = factory(App\Models\Messages\Thread::class)->create([
            'subject' => 'Messenger Party',
            'image' => '3.png'
        ]);
        $this->makeAdminGroupParticipant($all->whereIn('email', $this->admins), $thread);
        $all->whereNotIn('email', $this->admins)->each(function ($user) use($thread){
            Participant::create([
                'thread_id' => $thread->id,
                'owner_id' => $user->id,
                'owner_type' => 'App\User'
            ]);
        });
        for($x = 0; $x <= 40; $x++){
            factory(App\Models\Messages\Message::class)->create([
                'thread_id' => $thread->id,
                'owner_id' => $all->whereIn('email', $this->admins)->random()->id,
                'owner_type' => 'App\User'
            ]);
            usleep(100000);
        }
    }
}
