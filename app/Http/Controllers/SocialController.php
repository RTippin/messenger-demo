<?php

namespace App\Http\Controllers;

use App\Services\Social\NetworksService;

class SocialController extends Controller
{
    protected $networksService;
    public function __construct(NetworksService $networksService)
    {
        $this->networksService = $networksService;
    }

    public function handleNetworks()
    {
        $dispatch = $this->networksService->handleActions();
        if(!$dispatch['state']){
            return response()->json(['errors' => ['forms' => $dispatch['error']]], 400);
        }
        return response()->json(['status' => 'OK', 'msg' => $dispatch['msg'], 'case' => $dispatch['case']]);
    }
}
