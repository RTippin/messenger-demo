<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Cache;
use Validator;
use Exception;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    protected $redirectTo = '/messenger';
    protected $decayMinutes = 5;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->middleware('guest', ['except' => 'logout']);
    }

    protected function credentials()
    {
        return array_merge($this->request->only($this->username(), 'password'), ['active' => 1]);
    }

    public function login(Request $request)
    {
        $validate = $this->validateLogin($request);
        if($validate->fails()){
            return response()->json(['errors' => ['forms' => $validate->errors()], 'type' => 3], 400);
        }

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }
        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }
        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse();
    }

    public function validateLogin(Request $request)
    {
        return $validator = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'password' => 'required|string'
            ]
        );
    }

    protected function logout()
    {
        if(auth()->check()){
            Cache::forget(messenger_alias().'_online_'.messenger_profile()->id);
            Cache::forget(messenger_alias().'_away_'.messenger_profile()->id);
        }
        $this->guard()->logout();
        $this->request->session()->invalidate();
        if($this->request->expectsJson()){
            return response()->json(['status' => 1]);
        }
        return $this->loggedOut($this->request) ?: redirect('/');
    }

    protected function sendFailedLoginResponse()
    {
        return response()->json(['error' => 'These credentials do not match our records', 'type' => 0], 401);
    }

    public function authenticated($request, $user)
    {
        try{
            $user->messenger->timezone = geoip()->getLocation($this->request->ip())->getAttribute('timezone');
            $user->messenger->ip = $this->request->ip();
            $user->messenger->save();
        }catch (Exception $e){
            report($e);
        }
        return response()->json([
            'auth' => auth()->check()
        ]);
    }

}
