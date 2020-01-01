<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Jenssegers\Agent\Agent;
use View;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $request, $auth, $user_agent;
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->auth = auth()->user() ? auth()->user() : auth('api')->user();
        $this->user_agent = new Agent();
        View::share('user', $this->auth);
        View::share('current_model', $this->auth);
    }

    public function authType()
    {
        if($this->auth){
            return 1;
        }
        return 0;
    }

    public function modelType()
    {
        return $this->auth;
    }
}
