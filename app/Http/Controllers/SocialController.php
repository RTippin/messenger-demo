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

    public function add()
    {
        $dispatch = $this->networksService->handleActions('add');
        if(!$dispatch['state']){
            return response()->json([
                'errors' => [
                    'forms' => $dispatch['error']
                ]
            ], 400);
        }
        return response()->json([
            'msg' => $dispatch['msg'],
            'case' => $dispatch['case']
        ]);
    }

    public function remove()
    {
        $dispatch = $this->networksService->handleActions('remove');
        if(!$dispatch['state']){
            return response()->json([
                'errors' => [
                    'forms' => $dispatch['error']
                ]
            ], 400);
        }
        return response()->json([
            'msg' => $dispatch['msg'],
            'case' => $dispatch['case']
        ]);
    }

    public function cancel()
    {
        $dispatch = $this->networksService->handleActions('cancel');
        if(!$dispatch['state']){
            return response()->json([
                'errors' => [
                    'forms' => $dispatch['error']
                ]
            ], 400);
        }
        return response()->json([
            'msg' => $dispatch['msg'],
            'case' => $dispatch['case']
        ]);
    }

    public function accept()
    {
        $dispatch = $this->networksService->handleActions('accept');
        if(!$dispatch['state']){
            return response()->json([
                'errors' => [
                    'forms' => $dispatch['error']
                ]
            ], 400);
        }
        return response()->json([
            'msg' => $dispatch['msg'],
            'case' => $dispatch['case']
        ]);
    }

    public function deny()
    {
        $dispatch = $this->networksService->handleActions('deny');
        if(!$dispatch['state']){
            return response()->json([
                'errors' => [
                    'forms' => $dispatch['error']
                ]
            ], 400);
        }
        return response()->json([
            'msg' => $dispatch['msg'],
            'case' => $dispatch['case']
        ]);
    }

    public function getSentFriends()
    {
        return response()->json([
            'sent_friends' => NetworksService::MakeNetworkSentRequest()
        ], 200);
    }

    public function getPendingFriends()
    {
        return response()->json([
            'pending_friends' => NetworksService::MakeNetworkPendingRequest()
        ], 200);
    }

    public function getMyFriends()
    {
        return response()->json([
            'friends' => NetworksService::MakeMyFriends()
        ], 200);
    }
}
