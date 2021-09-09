<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use RTippin\Messenger\Models\Participant;
use RTippin\Messenger\Models\Thread;

class ThreadsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $users = User::all();

        $this->makePrivates($users);

        $this->makeGroupThread($users);
    }

    /**
     * Make private threads between ALL users.
     *
     * @param  Collection  $users
     */
    private function makePrivates(Collection $users): void
    {
        foreach ($users as $user) {
            $others = $users->where('email', '!=', $user->email)->all();

            foreach ($others as $other) {
                if (Thread::hasProvider($user)
                    ->join('participants as recipients', 'recipients.thread_id', '=', 'threads.id')
                    ->where('recipients.owner_id', '=', $other->getKey())
                    ->where('recipients.owner_type', '=', $other->getMorphClass())
                    ->whereNull('recipients.deleted_at')
                    ->private()
                    ->doesntExist()) {
                    $private = Thread::factory()->create();
                    Participant::factory()->for($private)->owner($user)->read()->create();
                    Participant::factory()->for($private)->owner($other)->read()->create();
                }
            }
        }
    }

    /**
     * Make initial group thread all users are in.
     *
     * @param  Collection  $users
     */
    private function makeGroupThread(Collection $users): void
    {
        $group = Thread::factory()->group()->create(['subject' => 'Messenger Party']);
        $admin = $users->firstWhere('email', '=', DatabaseSeeder::Admin['email']);
        $others = $users->where('email', '!=', DatabaseSeeder::Admin['email'])->all();

        // Make admin user the group admin
        Participant::factory()->for($group)->owner($admin)->admin()->create();

        foreach ($others as $other) {
            Participant::factory()->for($group)->owner($other)->create([
                'start_calls' => true,
            ]);
        }
    }
}
