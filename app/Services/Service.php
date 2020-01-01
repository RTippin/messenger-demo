<?php

namespace App\Services;

use Illuminate\Http\Request;

class Service
{

    protected $request, $auth;
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->auth = auth()->user();
    }

    /**
     * @return bool -> We get auth type of model requesting privacy, and run checks (auth type, is owner, is in network, is employee/employer
     */
    public function authType()
    {
        if($this->auth){
            return 1;
        }
        return 0;
    }

    public function currentProfile()
    {
        return $this->modelType();
    }

    public function modelType()
    {
        return $this->auth;
    }
}
