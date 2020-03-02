<?php

namespace App\Services\Messenger;

use App\Jobs\EndEmptyCall;
use App\Models\Messages\CallParticipants;
use App\Models\Messages\Calls;
use App\Models\Messages\Participant;
use App\Models\Messages\Thread;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class CallService
{
    public static function AuthCallSocket($thread_id, $call_id)
    {
        $thread = ThreadService::LocateThread($thread_id, ['participants']);
        if($thread && $thread instanceof Thread){
            $participant = ParticipantService::LocateParticipant($thread);
            $call = self::LocateActiveCall($thread);
            if($participant && $call && $call->id === $call_id) return true;
        }
        return false;
    }

    public static function LocateCallByID(Thread $thread, $id)
    {
        return $thread->calls->firstWhere('id', $id);
    }

    public static function LocateGlobalCallById($id)
    {
        return Calls::with(['thread'])->find($id);
    }

    public static function IsCall(Calls $call)
    {
        return $call->type === 1;
    }

    public static function IsCallAdmin(Thread $thread, Calls $call, Participant $participant)
    {
        return (ThreadService::IsThreadAdmin($thread, $participant) || $call->owner->id === messenger_profile()->id) ? true : false;
    }

    public static function LocateActiveCall(Thread $thread)
    {
        return $thread->activeCall;
    }

    private static function LocateCallParticipant(Calls $call)
    {
        return $call->participants->firstWhere('owner_id', messenger_profile()->id);
    }

    private static function LocateActiveParticipants(Calls $call)
    {
        return $call->participants->where('left_call', null);
    }

    public static function CallActiveCount(Calls $call)
    {
        $results = self::LocateActiveParticipants($call);
        return $results ? $results->count() : 0;
    }

    public static function IsInCall(Calls $call)
    {
        $locate = $call->participants->where('owner_id', messenger_profile()->id)->where('left_call', null)->first();
        if($locate) return true;
        return false;
    }

    public static function HasLeftCall(Calls $call)
    {
        $locate = $call->participants->where('owner_id', messenger_profile()->id)->where('left_call', '!=', null)->first();
        if($locate) return true;
        return false;
    }

    private static function StoreCall(Thread $thread, $attr = ['type' => 1, 'mode' => 1])
    {
        try{
            $call = new Calls();
            $call->thread_id = $thread->id;
            $call->owner_id = messenger_profile()->id;
            $call->owner_type = get_class(messenger_profile());
            $call->type = $attr['type'];
            $call->mode = $attr['mode'];
            $call->save();
            return $call;
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    private static function RemoveCall(Calls $call)
    {
        try{
            $call->delete();
        }catch (Exception $e){
            report($e);
        }
    }

    private static function StoreOrRestoreParticipant(Calls $call)
    {
        try{
            $participant_exist = self::LocateCallParticipant($call);
            if($participant_exist){
                if(!is_null($participant_exist->left_call)){
                    $participant_exist->left_call = null;
                    $participant_exist->save();
                }
            }
            else{
                $participant = new CallParticipants();
                $participant->call_id = $call->id;
                $participant->owner_id = messenger_profile()->id;
                $participant->owner_type = get_class(messenger_profile());
                $participant->save();
            }
            Redis::setex('call:'.$call->id.":".messenger_profile()->id, 31, messenger_profile()->id);
            return true;
        }catch (Exception $e){
            report($e);
            return false;
        }
    }

    private static function PerformCallStartup(Thread $thread, Calls $call, $mode)
    {
        try{
            $add = self::StoreOrRestoreParticipant($call);
            if($add){
                switch ($mode){
                    case 'call':
                        (new BroadcastService($thread))->broadcastChannels()->broadcastCall($call);
                    break;
                    case 'other_mode':

                    break;
                }
                return true;
            }
        }catch (Exception $e){
            report($e);
        }
        return false;
    }

    private static function ParticipantLeftCall(CallParticipants $participant)
    {
        try{
            $participant->left_call = Carbon::now();
            $participant->save();
        }catch (Exception $e){
            report($e);
        }
    }

    private static function CallEnded(Calls $call)
    {
        try{
            $call->active = 0;
            $call->call_ended = Carbon::now();
            $call->save();
            return true;
        }catch (Exception $e){
            report($e);
        }
        return false;
    }

    public static function PerformCallShutdown(Thread $thread, Calls $call)
    {
        $thread_participants = collect([]);
         if(self::CallEnded($call)){
             $active = self::LocateActiveParticipants($call);
             foreach($active as $participant){
                 self::ParticipantLeftCall($participant);
                 $thread_participant = ParticipantService::LocateParticipant($thread, $participant->owner);
                 if($thread_participant) $thread_participants->push($thread_participant);
             }
             MessageService::StoreSystemMessage($thread, $call->owner, collect(["call_id" => $call->id]), 90);
             (new BroadcastService($thread))->broadcastChannels(true)->broadcastCallEnded($call);
             try{
                 $thread_participants->each(function ($participant){
                     ParticipantService::MarkRead($participant);
                 });
             }catch (Exception $e){
                 report($e);
             }
         }
    }

    public static function CallHeartbeat(Request $request, Thread $thread)
    {
        $current_call = self::LocateActiveCall($thread);
        if($current_call && $request->call_id === $current_call->id){
            self::StoreOrRestoreParticipant($current_call);
            return [
                'state' => true,
                'data' => true
            ];
        }
        return [
            'state' => false,
            'error' => 'Call not found'
        ];
    }

    public static function StartNewCall(Thread $thread = null, Participant $participant = null, $mode = 'call')
    {
        if(!config('messenger.calls')){
            return [
                'state' => false,
                'error' => 'This feature is currently unavailable, please try again later'
            ];
        }
        if(!ThreadService::CanStartCall($thread, $participant)){
            return [
                'state' => false,
                'error' => 'Access Denied'
            ];
        }
        $current_call = self::LocateActiveCall($thread);
        if($current_call){
            if(self::CallActiveCount($current_call) && self::StoreOrRestoreParticipant($current_call)){
                return [
                    'state' => true,
                    'data' => MessengerRepo::MakeCall($thread, $current_call)
                ];
            }
            self::PerformCallShutdown($thread, $current_call);
        }
        $new_call = null;
        switch ($mode){
            case 'call':
                $new_call = self::StoreCall($thread);
            break;
            case 'other_mode':

            break;
        }
        if($new_call){
            if(self::PerformCallStartup($thread, $new_call, $mode)){
                return [
                    'state' => true,
                    'data' => MessengerRepo::MakeCall($thread, $new_call)
                ];
            }
            self::RemoveCall($new_call);
        }
        return [
            'state' => false,
            'error' => 'Server Error'
        ];
    }

    public static function JoinCall(Thread $thread)
    {
        if(!config('messenger.calls')){
            return [
                'state' => false,
                'error' => 'This feature is currently unavailable, please try again later'
            ];
        }
        $current_call = self::LocateActiveCall($thread);
        if($current_call && self::StoreOrRestoreParticipant($current_call)){
            return [
                'state' => true,
                'data' => MessengerRepo::MakeCall($thread, $current_call)
            ];
        }
        return [
            'state' => false,
            'error' => 'There are no current calls to join'
        ];
    }

    public static function LeaveCall(Thread $thread)
    {
        $current_call = self::LocateActiveCall($thread);
        $count = 0;
        if($current_call){
            $count = self::CallActiveCount($current_call);
            $participant = self::LocateCallParticipant($current_call);
            if($participant && !$participant->left_call){
                self::ParticipantLeftCall($participant);
                $count = $count-1;
            }
            if($count <= 0) self::PerformCallShutdown($thread, $current_call);
        }
        return [
            'state' => true,
            'data' => [
                'count' => $count
            ]
        ];
    }

    public static function EndCall(Thread $thread, Participant $participant)
    {
        $current_call = self::LocateActiveCall($thread);
        if($current_call && ThreadService::CanEndCall($thread, $participant, $current_call)){
            self::PerformCallShutdown($thread, $current_call);
            return [
                'state' => true,
                'data' => true
            ];
        }
        return [
            'state' => false,
            'error' => 'Access Denied'
        ];
    }

    public static function ViewCall(Request $request, Thread $thread, Participant $participant)
    {
        $current_call = self::LocateActiveCall($thread);
        if($current_call && $current_call->id === $request->call_id){
            return [
                'state' => true,
                'data' => [
                    'call' => $current_call,
                    'thread' => $thread,
                    'call_admin' => self::IsCallAdmin($thread, $current_call, $participant),
                    'thread_admin' => ThreadService::IsThreadAdmin($thread, $participant)
                ]
            ];
        }
        return [
            'state' => false,
            'error' => 'That call does not exist'
        ];
    }

    /**
     * This is run every minute via the scheduler
     * Locate active calls, end calls with no active participants,
     * and find active participants who are not in redis and mark as left
     * @param bool $now
     */
    public static function CallParticipantChecks($now = false)
    {
        $activeCalls = Calls::with('participants')->where('active', 1)->get();
        foreach($activeCalls as $call){
            if($call->created_at->diffInSeconds(Carbon::now()) < 60){
                continue;
            }
            if(!self::CallActiveCount($call)){
                $now ? self::PerformCallShutdown($call->thread, $call) : EndEmptyCall::dispatch($call->id);
                continue;
            }
            foreach(self::LocateActiveParticipants($call) as $participant){
                if(!Redis::get('call:'.$call->id.":".$participant->owner_id)){
                    self::ParticipantLeftCall($participant);
                }
            }
        }
        return;
    }

    public static function ActiveCallsExist()
    {
        if(Calls::where('active', 1)->count()) return true;
        return false;
    }

}
