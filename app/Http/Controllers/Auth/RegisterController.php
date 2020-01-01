<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\RegisterService;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use RegistersUsers;
    protected $redirectTo = '/';
    protected $registerService;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->middleware('Registration');
        $this->middleware('guest');
        $this->registerService = new RegisterService($request);
    }

    public function register()
    {
        $dispatch = $this->registerService->registerPost();
        if(!$dispatch['state']){
            return response()->json(['errors' => ['forms' => $dispatch['error']] , 'registered' => false], 400);
        }
        return response()->json(['registered' => true, 'location' => $dispatch['location']]);
    }
}
