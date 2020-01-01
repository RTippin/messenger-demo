<?php

namespace App\Http\Controllers;

use App\Services\Social\NetworksService;

class SocialController extends Controller
{
    public function handleNetworks()
    {
        $dispatch = new NetworksService($this->request);
        $dispatch = $dispatch->handleActions();
        if(!$dispatch['state']){
            return response()->json(['errors' => ['forms' => $dispatch['error']]], 400);
        }
        return response()->json(['status' => 'OK', 'msg' => $dispatch['msg'], 'case' => $dispatch['case']]);
    }
}