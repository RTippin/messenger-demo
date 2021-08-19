<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Exception;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use RTippin\Messenger\Models\Friend;
use RTippin\Messenger\Models\Message;
use RTippin\Messenger\Models\Messenger;
use RTippin\Messenger\Models\Participant;
use RTippin\Messenger\Models\Thread;
use Throwable;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
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
     * @param array $data
     * @return User
     * @throws Throwable
     */
    protected function create(array $data): User
    {
        // When a new user registers, let us auto add them as
        // friends to admin and add to the base first group.

        DB::beginTransaction();

        try {
            $admin = User::whereEmail('admin@example.net')->first();
            $group = Thread::group()->oldest()->first();
            $private = Thread::factory()->create();
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'demo' => false,
                'admin' => false,
                'password' => Hash::make($data['password']),
            ]);
            Messenger::factory()->owner($user)->create();
            Friend::factory()->providers($admin, $user)->create();
            Friend::factory()->providers($user, $admin)->create();
            Participant::factory()->for($group)->owner($user)->create();
            Participant::factory()->for($private)->owner($admin)->create();
            Participant::factory()->for($private)->owner($user)->create();
            Message::factory()->for($private)->owner($admin)->create(['body' => 'Welcome to the messenger demo!']);

            DB::commit();
        } catch (Exception $e) {
            report($e);

            DB::rollBack();

            abort(500);
        }

        return $user;
    }
}
