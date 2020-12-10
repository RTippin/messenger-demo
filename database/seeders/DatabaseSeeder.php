<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use RTippin\Messenger\Actions\Threads\StoreParticipant;
use RTippin\Messenger\Definitions;
use RTippin\Messenger\Facades\Messenger;
use RTippin\Messenger\Models\Friend;
use RTippin\Messenger\Models\Message;
use RTippin\Messenger\Models\Thread;

class DatabaseSeeder extends Seeder
{
    /**
     * @var User
     */
    private User $admin;

    /**
     * @var StoreParticipant
     */
    private StoreParticipant $storeParticipant;

    /**
     * DatabaseSeeder constructor.
     *
     * @param StoreParticipant $storeParticipant
     */
    public function __construct(StoreParticipant $storeParticipant)
    {
        $this->storeParticipant = $storeParticipant;
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->admin = User::create([
            'name' => 'John Doe',
            'email' => 'admin@example.net',
            'admin' => true,
            'password' => '$2y$10$rb4NakT8uw00mOPSUaaxMe4Ogy5ja8PUIgkdMhQQxa.apOO8wTI4a'
        ]);

        Messenger::getProviderMessenger($this->admin); //creates messenger entry

        User::factory()
            ->count(15)
            ->create();

        Company::factory()
            ->count(5)
            ->create();

        $others = User::where('email', '!=', $this->admin->email)->get();

        $companies = Company::all();

        $groupThread = Thread::create([
            'type' => 2,
            'subject' => 'Messenger Party',
            'image' => rand(1,5).'.png',
            'add_participants' => 1,
            'invitations' => 1,
            'calling' => 1,
            'knocks' => 1,
            'lockout' => 0
        ]);

        $this->storeParticipant->execute($groupThread, $this->admin, Definitions::DefaultAdminParticipant);

        $others->each(
            fn(User $user) => $this->storeParticipant->execute($groupThread, $user, Definitions::DefaultParticipant)
        );

        $companies->each(
            fn(Company $company) => $this->storeParticipant->execute($groupThread, $company, Definitions::DefaultParticipant)
        );

        for($x = 0; $x < 25; $x++){
            Message::factory()
                ->for($groupThread)
                ->create([
                    'owner_id' => $others->random()->id,
                    'owner_type' => 'App\Models\User'
                ]);
        }

        $others->each(fn(User $user) => $this->makePrivateThreadAndFriend($user));
    }

    /**
     * @param User $user
     */
    private function makePrivateThreadAndFriend(User $user)
    {
        Friend::create([
            'owner_id' => $this->admin->id,
            'owner_type' => 'App\Models\User',
            'party_id' => $user->id,
            'party_type' => 'App\Models\User'
        ]);

        Friend::create([
            'owner_id' => $user->id,
            'owner_type' => 'App\Models\User',
            'party_id' => $this->admin->id,
            'party_type' => 'App\Models\User'
        ]);

        $thread = Thread::create(Definitions::DefaultThread);

        $this->storeParticipant->execute($thread, $this->admin, Definitions::DefaultParticipant)
            ->execute($thread, $user, Definitions::DefaultParticipant);

        $ids = collect([$user->id, $this->admin->id]);

        for($x = 0; $x < 15; $x++){
            Message::factory()
                ->for($thread)
                ->create([
                    'owner_id' => $ids->random(),
                    'owner_type' => 'App\Models\User'
                ]);
        }
    }
}
