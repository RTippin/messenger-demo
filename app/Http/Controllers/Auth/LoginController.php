<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Hash;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Cache;
use Validator;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    protected $redirectTo = '/messenger';
    protected $decayMinutes = 5;

    public function __construct(Request $request)
    {
        parent::__construct($request);
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
            Cache::forget(strtolower(class_basename($this->modelType())).'_online_'.$this->modelType()->id);
            Cache::forget(strtolower(class_basename($this->modelType())).'_away_'.$this->modelType()->id);
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

    protected function authenticated($request, $user)
    {
        $intended = session()->get('url.intended');
        $user->messengerSettings->touch();
        return response()->json(['auth' => auth()->check(),'intended' => ($intended ? $intended : 'reload')]);
    }

}
