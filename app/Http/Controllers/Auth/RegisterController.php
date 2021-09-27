<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use RTippin\Messenger\Exceptions\InvalidProviderException;
use RTippin\Messenger\Exceptions\MessengerComposerException;
use RTippin\Messenger\Facades\MessengerComposer;
use RTippin\Messenger\Models\Friend;
use RTippin\Messenger\Models\Messenger;
use RTippin\Messenger\Models\Participant;
use RTippin\Messenger\Models\Thread;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default, this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected string $redirectTo = RouteServiceProvider::HOME;

    /**
     * @var User
     */
    private User $newUser;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     *
     * @throws Throwable
     */
    protected function create(array $data): User
    {
        try {
            DB::transaction(fn () => $this->makeUser($data));
        } catch (Throwable $e) {
            report($e);

            throw new HttpException(500, 'Registration failed.');
        }

        return $this->newUser;
    }

    /**
     * @param  array  $data
     *
     * @throws InvalidProviderException|MessengerComposerException|Throwable
     */
    private function makeUser(array $data): void
    {
        $this->newUser = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'demo' => false,
            'admin' => false,
            'password' => Hash::make($data['password']),
        ]);

        Messenger::factory()->owner($this->newUser)->create();

        // Remove this method call if you do not want new
        // users to be setup with the admin account.
        $this->setupUserWithDemo();
    }

    /**
     * @throws Throwable
     * @throws InvalidProviderException|MessengerComposerException
     */
    private function setupUserWithDemo(): void
    {
        $admin = User::whereEmail(DatabaseSeeder::Admin['email'])->first();
        $group = Thread::group()->oldest()->first();
        Friend::factory()->providers($admin, $this->newUser)->create();
        Friend::factory()->providers($this->newUser, $admin)->create();
        Participant::factory()->for($group)->owner($this->newUser)->create([
            'start_calls' => true,
            'send_knocks' => true,
            'add_participants' => true,
            'manage_invites' => true,
        ]);
        MessengerComposer::to($this->newUser)
            ->from($admin)
            ->message('Welcome to the messenger demo!');
    }
}
