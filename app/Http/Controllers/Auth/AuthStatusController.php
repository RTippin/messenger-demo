<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Messenger\MessengerLocationService;
use App\Services\Messenger\MessengerRepo;
use Cache;
use Illuminate\Http\Request;
use Auth;
use Exception;


class AuthStatusController extends Controller
{
    protected $request;
    /**
     * @var MessengerLocationService
     */
    protected $messengerLocationService;

    public function __construct(Request $request, MessengerLocationService $messengerLocationService)
    {
        $this->request = $request;
        if(Auth::check()){
            $this->middleware('IsActive');
        }
        $this->messengerLocationService = $messengerLocationService;
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
            $away = false;
            $threads = 0;
            $friend_request = 0;
            $active_calls = [];
            if(auth()->check()){
                if($this->request->isMethod('post')){
                    switch($this->request->input('status')){
                        case 1:
                            $this->markOnline();
                        break;
                        case 2:
                            if(!Cache::has(messenger_alias().'_online_'.messenger_profile()->id)){
                                $this->markAway();
                                $away = true;
                            }
                        break;
                    }
                }
                else{
                    if(Cache::has(messenger_alias().'_away_'.messenger_profile()->id)){
                        $this->markAway();
                        $away = true;
                    }
                    else{
                        $this->markOnline();
                    }
                }
                try{
                    if($this->request->ip() !== messenger_profile()->messenger->ip){
                        $this->messengerLocationService->update();
                    }
                    else{
                        messenger_profile()->messenger->touch();
                    }
                    $threads = messenger_profile()->unreadThreadsCount();
                    $active_calls = MessengerRepo::MakeActiveCalls();
                    if(messenger_profile()->pendingReceivedNetworks->count()) $friend_request = messenger_profile()->pendingReceivedNetworks->count();
                }catch (Exception $e){
                    report($e);
                }
            }
            return response()->json([
                'auth' => auth()->check(),
                'token' => csrf_token(),
                'model' => (auth()->check() ? messenger_alias() : 'guest'),
                'states' => (auth()->check() ? [
                    'away' => $away,
                    'unread_threads_count' => $threads,
                    'pending_friends_count' => $friend_request,
                    'active_calls' => $active_calls
                ] : null)
            ], 200);
        }
        return redirect('/login');
    }
}
