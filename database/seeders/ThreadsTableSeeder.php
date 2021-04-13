<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use RTippin\Messenger\Models\Participant;
use RTippin\Messenger\Models\Thread;

class ThreadsTableSeeder extends Seeder
{
    /**
     * @var Collection
     */
    private Collection $users;

    /**
     * @var Collection
     */
    private Collection $companies;

    /**
     * @var User
     */
    private User $admin;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->admin = User::where('email', '=', 'admin@example.net')->first();
        $this->users = User::where('admin', '=', false)->get();
        $this->companies = Company::all();
        $this->makeGroupThread();
        $this->makePrivateThreadsWithAdmin();
    }

    /**
     * Make initial group thread all users are in.
     */
    private function makeGroupThread(): void
    {
        $group = Thread::factory()->group()->create(['subject' => 'Messenger Party']);
        Participant::factory()->for($group)->owner($this->admin)->admin()->create();
        $this->users->each(function(User $user) use ($group) {
            Participant::factory()->for($group)->owner($user)->create();
        });
        $this->companies->each(function(Company $company) use ($group) {
            Participant::factory()->for($group)->owner($company)->create();
        });
    }

    /**
     * Make private thread with admin and all other users.
     */
    private function makePrivateThreadsWithAdmin(): void
    {
        $this->users->each(function(User $user) {
            $private = Thread::factory()->create();
            Participant::factory()->for($private)->owner($this->admin)->create();
            Participant::factory()->for($private)->owner($user)->create();
        });
    }
}
