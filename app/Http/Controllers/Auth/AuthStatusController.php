<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Messenger\MessengerRepo;
use App\Services\Social\NetworksService;
use Cache;
use Illuminate\Http\Request;
use Auth;
use Exception;


class AuthStatusController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        if(Auth::check()){
            $this->middleware('IsActive');
        }
    }

    private function markOnline()
    {
        Cache::forget(strtolower(class_basename($this->modelType())).'_away_'.$this->modelType()->id);
        Cache::put(strtolower(class_basename($this->modelType())).'_online_'.$this->modelType()->id, true, now()->addMinutes(2));
    }

    private function markAway()
    {
        Cache::forget(strtolower(class_basename($this->modelType())).'_online_'.$this->modelType()->id);
        Cache::put(strtolower(class_basename($this->modelType())).'_away_'.$this->modelType()->id, true, now()->addMinutes(2));
    }

    public function authHeartBeat()
    {
        if($this->request->expectsJson()){
            $notify = 0;
            $threads = 0;
            $active_calls = [];
            $network_request = [];
            if(auth()->check()){
                if($this->request->isMethod('post')){
                    switch($this->request->input('status')){
                        case 1:
                            $this->markOnline();
                        break;
                        case 2:
                            $this->markAway();
                        break;
                    }
                }
                else{
                    if(Cache::has(strtolower(class_basename($this->modelType())).'_away_'.$this->modelType()->id)){
                        $this->markAway();
                    }
                    else{
                        $this->markOnline();
                    }
                }
                try{
                    $this->modelType()->messengerSettings->touch();
                    $notify = $this->modelType()->unreadNotifications->count();
                    $threads = $this->modelType()->unreadThreadsCount();
                    $active_calls = MessengerRepo::MakeActiveCalls($this->modelType());
                    if($this->modelType()->pendingReceivedNetworks->count()) $network_request = NetworksService::MakeNetworkRequest($this->modelType());
                }catch (Exception $e){
                    report($e);
                }
            }
            return response()->json([
                'auth' => auth()->check(),
                'token' => csrf_token(),
                'model' => (auth()->check() ? strtolower(class_basename($this->modelType())) : 'guest'),
                'states' => (auth()->check() ? [
                    'unread_notify_count' => $notify,
                    'unread_threads_count' => $threads,
                    'active_calls' => $active_calls,
                    'pending_friends' => $network_request
                ] : null)
            ], 200);
        }
        return redirect('/login');
    }
}