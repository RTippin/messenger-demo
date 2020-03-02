<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginLoggerService;
use App\Services\Messenger\MessengerLocationService;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Cache;
use Validator;
use Exception;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * @var string
     */
    protected $redirectTo = '/messenger';

    /**
     * @var int
     */
    protected $decayMinutes = 5;

    /**
     * @var Request
     */
    protected $request;
    /**
     * @var LoginLoggerService
     */
    protected $loggerService;
    /**
     * @var MessengerLocationService
     */
    protected $messengerLocationService;

    /**
     * LoginController constructor.
     * @param Request $request
     * @param LoginLoggerService $loggerService
     * @param MessengerLocationService $messengerLocationService
     */
    public function __construct(Request $request,
                                LoginLoggerService $loggerService,
                                MessengerLocationService $messengerLocationService
    )
    {
        $this->request = $request;
        $this->middleware('guest', ['except' => 'logout']);
        $this->loggerService = $loggerService;
        $this->messengerLocationService = $messengerLocationService;
    }

    /**
     * @return array
     */
    protected function credentials()
    {
        return array_merge($this->request->only($this->username(), 'password'), ['active' => 1]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|void
     * @throws \Illuminate\Validation\ValidationException
     */
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

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validateLogin(Request $request)
    {
        return $validator = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'password' => 'required|string'
            ]
        );
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
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

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendFailedLoginResponse()
    {
        return response()->json(['error' => 'These credentials do not match our records', 'type' => 0], 401);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    protected function authenticated(Request $request, User $user)
    {
        try{
            set_messenger_profile($user);
            $this->loggerService->store($user);
            $this->messengerLocationService->update();
        }catch (Exception $e){
            report($e);
        }
        return response()->json([
            'auth' => auth()->check()
        ]);
    }

}
