<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use RTippin\Messenger\Actions\Threads\StoreManyParticipants;
use RTippin\Messenger\Facades\Messenger;
use RTippin\Messenger\Models\Friend;
use RTippin\Messenger\Models\Thread;

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
    protected $redirectTo = RouteServiceProvider::HOME;

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
    protected function validator(array $data)
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
     * @return \App\Models\User
     * @throws \Throwable
     */
    protected function create(array $data)
    {
        //custom stuff so new user auto adds to demo group

        DB::beginTransaction();

        try{
            $admin = User::whereEmail('admin@example.net')->first();

            $group = Thread::group()->oldest()->first();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'demo' => false,
                'admin' => false,
                'password' => Hash::make($data['password']),
            ]);

            Messenger::getProviderMessenger($user);

            Friend::create([
                'owner_id' => $admin->id,
                'owner_type' => 'App\Models\User',
                'party_id' => $user->id,
                'party_type' => 'App\Models\User'
            ]);

            Friend::create([
                'owner_id' => $user->id,
                'owner_type' => 'App\Models\User',
                'party_id' => $admin->id,
                'party_type' => 'App\Models\User'
            ]);

            Messenger::setProvider($admin);
            app(StoreManyParticipants::class)->execute($group, [
                    [
                        'alias' => 'user',
                        'id' => $user->id
                    ]

            ]);
            Messenger::unsetProvider();

            DB::commit();

            return $user;

        }catch (Exception $e){
            report($e);

            DB::rollBack();
        }

        abort(500);
    }
}
