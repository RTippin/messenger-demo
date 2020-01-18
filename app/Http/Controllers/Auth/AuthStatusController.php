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
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        if(Auth::check()){
            $this->middleware('IsActive');
        }
    }

    private function markOnline()
    {
        Cache::forget(messenger_alias().'_away_'.messenger_profile()->id);
        Cache::put(messenger_alias().'_online_'.messenger_profile()->id, true, now()->addMinutes(2));
    }

    private function markAway()
    {
        Cache::forget(messenger_alias().'_online_'.messenger_profile()->id);
        Cache::put(messenger_alias().'_away_'.messenger_profile()->id, true, now()->addMinutes(2));
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
                    if(Cache::has(messenger_alias().'_away_'.messenger_profile()->id)){
                        $this->markAway();
                    }
                    else{
                        $this->markOnline();
                    }
                }
                try{
                    if($this->request->ip() !== messenger_profile()->messenger->ip){
                        messenger_profile()->messenger->ip = $this->request->ip();
                        messenger_profile()->messenger->timezone = geoip()->getLocation($this->request->ip())->getAttribute('timezone');
                        messenger_profile()->messenger->save();
                    }
                    else{
                        messenger_profile()->messenger->touch();
                    }
                    $threads = messenger_profile()->unreadThreadsCount();
                    $active_calls = MessengerRepo::MakeActiveCalls();
                    if(messenger_profile()->pendingReceivedNetworks->count()) $network_request = NetworksService::MakeNetworkRequest();
                }catch (Exception $e){
                    report($e);
                }
            }
            return response()->json([
                'auth' => auth()->check(),
                'token' => csrf_token(),
                'model' => (auth()->check() ? messenger_alias() : 'guest'),
                'states' => (auth()->check() ? [
                    'unread_threads_count' => $threads,
                    'active_calls' => $active_calls,
                    'pending_friends' => $network_request
                ] : null)
            ], 200);
        }
        return redirect('/login');
    }
}
