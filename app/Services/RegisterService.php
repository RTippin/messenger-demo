<?php

namespace App\Services;

use App\Http\Requests\Register;
use App\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Hash;
use Str;
use Exception;

class RegisterService
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function registerPost()
    {
        $validate = $this->validate();
        if(!$validate['state']){
            return $validate;
        }
        return $this->registerActions();
    }

    private function registerActions()
    {
        $newUser = $this->makeUser();
        if($newUser && self::makeUserMessenger($newUser)){
            Auth::guard()->login($newUser);
            return [
                'state' => true
            ];
        }
        return [
            'state' => false,
            'error' => 'Server Error'
        ];
    }

    private function makeUser()
    {
        try{
            return User::create([
                'first' => $this->request->input('first'),
                'last' => $this->request->input('last'),
                'email' => $this->request->input('email'),
                'password' => Hash::make($this->request->input('password'))
            ]);
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    private static function makeUserMessenger(User $user)
    {
        try{
            $user->messenger()->create([
                'slug' => Str::slug($user->last.' '.Str::random(4),'-').'_'.Carbon::now()->timestamp
            ]);
            return true;
        }catch (Exception $e){
            report($e);
            return false;
        }
    }

    private function validate()
    {
        $validate = (new Register($this->request))->validate();
        if($validate->fails()){
            return array("state" => false, "error" => $validate->errors());
        }
        return array("state" => true);
    }
}
