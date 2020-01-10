<?php

namespace App\Services\Messenger;

use App\Models\Messages\Participant;
use App\Models\Messages\Thread;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class ParticipantService
{
    /**
     * @param Thread $thread
     * @param $model
     * @return Participant|null
     */
    public static function LocateParticipant(Thread $thread, $model)
    {
        return $thread->participants->where('owner_id', $model->id)->where('owner_type', get_class($model))->first();
    }

    public static function LocateParticipantWithID(Thread $thread, $id)
    {
        return $thread->participants->firstWhere('id', $id);
    }

    public static function MarkRead(Participant $participant)
    {
        try{
            $participant->last_read = Carbon::now();
            $participant->save();
            return $participant;
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    public static function StoreParticipant(Thread $thread, $model, $admin = false)
    {
        try{
            $participant = new Participant();
            $participant->thread_id = $thread->id;
            $participant->owner_id = $model->id;
            $participant->owner_type = get_class($model);
            $participant->admin = $admin;
            $participant->save();
            return $participant;
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    public static function StoreOrRestoreParticipant(Thread $thread, $model)
    {
        try{
            return Participant::withTrashed()->firstOrCreate([
                'thread_id' => $thread->id,
                'owner_id' => $model->id,
                'owner_type' => get_class($model)
            ])->restore();
        }catch (Exception $e){
            report($e);
            return false;
        }
    }

    public static function AddParticipants(Thread $thread, Request $request, $model, $broadcast_log = true)
    {
        if(empty($request->input('recipients'))){
            return [
                'state' => false,
                'error' => 'No participants selected'
            ];
        }
        try{
            $names = ''; $ids = collect([]);
            foreach($request->input('recipients') as $key => $value){
                $arr = explode('_', $value['value']);
                $class = get_alias_class($arr[0]);
                if(!$class) continue;
                $profile = $class::find($arr[1]);
                if(!$profile || self::LocateParticipant($thread, $profile)) continue;
                if(self::StoreOrRestoreParticipant($thread, $profile)){
                    $names .= $profile->name.', ';
                    $ids->push(["owner_id" => $profile->id, "owner_type" => get_class($profile), "model" => $profile]);
                }
            }
            if(!$ids->count()){
                return [
                    'state' => false,
                    'error' => 'No valid participants found'
                ];
            }
            $call = CallService::LocateActiveCall($thread);
            if($call) (new BroadcastService($thread, $model))->broadcastChannels(false, false, true, $ids)->broadcastCall($call);
            (new BroadcastService($thread->fresh('participants.owner.devices'), $model))->broadcastChannels()->broadcastAddedToThread();
            MessageService::StoreSystemMessage($thread, $model, $ids, 99, $broadcast_log);
            return [
                'state' => true,
                'subject' => $thread->name,
                'names' => rtrim($names,', ')
            ];
        }catch (Exception $e){
            report($e);
            return [
                'state' => false,
                'error' => 'Server Error'
            ];
        }
    }

    private static function RemoveParticipant(Thread $thread, $model, Participant $participant, $kicked = false)
    {
        try {
            if($kicked) (new BroadcastService($thread, $model))->broadcastKicked($participant);
            MessageService::StoreSystemMessage($thread, $model, $kicked ? collect(["owner_id" => $participant->owner_id, "owner_type" => $participant->owner_type]) : 'left the group', $kicked ? 98 : 97);
            $participant->delete();
            return true;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    private static function ChangeParticipantAdmin(Thread $thread, $model, Participant $participant, $admin = false)
    {
        try{
            $participant->admin = $admin ? 1 : 0;
            $participant->save();
            MessageService::StoreSystemMessage($thread, $model, collect(["owner_id" => $participant->owner_id, "owner_type" => $participant->owner_type]), $admin ? 96 : 95);
            return $participant;
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    public static function KickParticipantGroup(Request $request, Thread $thread, Participant $participant, $model)
    {
        if(ThreadService::IsLocked($thread, $participant) || !ThreadService::IsThreadAdmin($thread, $participant)){
            return [
                'state' => false,
                'error' => 'Access denied'
            ];
        }
        $remove = $thread->participants->firstWhere('id', $request->input('p_id'));
        if($remove && self::RemoveParticipant($thread, $model, $remove, true)){
            return [
                'state' => true,
                'data' => 'You removed '.$remove->owner->name.' from the group'
            ];
        }
        return [
            'state' => false,
            'error' => 'Server Error'
        ];
    }

    public static function ModifyParticipantAdmin(Request $request, Thread $thread, Participant $participant, $model, $admin)
    {
        if(ThreadService::IsLocked($thread, $participant) || !ThreadService::IsThreadAdmin($thread, $participant)){
            return [
                'state' => false,
                'error' => 'Access denied'
            ];
        }
        $target = self::LocateParticipantWithID($thread, $request->input('p_id'));
        $update = self::ChangeParticipantAdmin($thread, $model, $target, $admin);
        if($target && $update){
            return [
                'state' => true,
                'data' => [
                    'participant' => $update,
                    'message' => ($admin ? "You promoted ".$target->owner->name." to admin" : "You revoked admin from ".$target->owner->name),
                    'admin' => $admin
                ]
            ];
        }
        return [
            'state' => false,
            'error' => 'Server Error'
        ];
    }

    public static function AddParticipantsGroupCheck(Request $request, Thread $thread, Participant $participant, $model)
    {
        if(!ThreadService::CanAddParticipants($thread, $participant)){
            return [
                'state' => false,
                'error' => 'Access Denied'
            ];
        }
        $adding = self::AddParticipants($thread, $request, $model);
        if($adding['state']){
            return [
                'state' => true,
                'data' => $adding
            ];
        }
        return [
            'state' => false,
            'error' => $adding['error']
        ];
    }

    public static function LeaveGroupCheck(Thread $thread, Participant $participant, $model)
    {
        if(!ThreadService::IsGroup($thread)){
            return [
                'state' => false,
                'error' => 'Must be a group thread'
            ];
        }
        $name = $thread->name;
        if(ThreadService::IsThreadAdmin($thread, $participant)){
            if($thread->participants->where('admin', 1)->count() === 1){
                switch($thread->participants->count()){
                    case 1:
                        self::RemoveParticipant($thread, $model, $participant);
                        ThreadService::RemoveThread($thread, $model);
                    break;
                    case 2:
                        $promote = self::ChangeParticipantAdmin($thread, $model, $thread->participants->firstWhere('admin', 0), true);
                        if(!$promote){
                            return [
                                'state' => false,
                                'error' => 'Unable to promote other to admin'
                            ];
                        }
                        self::RemoveParticipant($thread, $model, $participant);
                    break;
                    default:
                        if(ThreadService::IsLocked($thread, $participant)){
                            self::RemoveParticipant($thread, $model, $participant);
                        }
                        else{
                            return [
                                'state' => false,
                                'error' => 'It appears you are the only admin. You must designate another participant as an admin before you can exit this group'
                            ];
                        }
                }
            }
            else{
                self::RemoveParticipant($thread, $model, $participant);
            }
        }
        else{
            self::RemoveParticipant($thread, $model, $participant);
        }
        return [
            'state' => true,
            'data' => 'You left the group '.$name
        ];
    }

}
