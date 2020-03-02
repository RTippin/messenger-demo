<?php

namespace App\Services\Messenger;

use App\Models\Messages\Participant;
use App\Models\Messages\Thread;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ParticipantService
{
    /**
     * @param Thread $thread
     * @param Model $model
     * @return Participant|null
     */
    public static function LocateParticipant(Thread $thread, Model $model = null)
    {
        return $thread->participants->where('owner_id', ($model ? $model->id : messenger_profile()->id))->where('owner_type', get_class($model ? $model : messenger_profile()))->first();
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

    public static function StoreParticipant(Thread $thread, Model $model = null, $admin = false)
    {
        try{
            $participant = new Participant();
            $participant->thread_id = $thread->id;
            $participant->owner_id = $model ? $model->id : messenger_profile()->id;
            $participant->owner_type = get_class($model ? $model : messenger_profile());
            $participant->admin = $admin;
            $participant->save();
            return $participant;
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    public static function StoreOrRestoreParticipant(Thread $thread, Model $model = null)
    {
        try{
            return Participant::withTrashed()->firstOrCreate([
                'thread_id' => $thread->id,
                'owner_id' => $model ? $model->id : messenger_profile()->id,
                'owner_type' => get_class($model ? $model : messenger_profile())
            ])->restore();
        }catch (Exception $e){
            report($e);
            return false;
        }
    }

    public static function AddParticipants(Thread $thread, Request $request, $broadcast_log = true)
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
            if($call) (new BroadcastService($thread))->broadcastChannels(false, false, true, $ids)->broadcastCall($call);
            (new BroadcastService($thread))->broadcastChannels(false, false, true, $ids)->broadcastAddedToThread();
            MessageService::StoreSystemMessage($thread->fresh('participants.owner.devices'), messenger_profile(), $ids, 99, $broadcast_log);
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

    private static function RemoveParticipant(Thread $thread, Participant $participant, $kicked = false)
    {
        try {
            if($kicked) (new BroadcastService($thread))->broadcastKicked($participant);
            MessageService::StoreSystemMessage(
                $thread,
                messenger_profile(),
                $kicked ? collect(["owner_id" => $participant->owner_id, "owner_type" => $participant->owner_type]) : 'left the group', $kicked ? 98 : 97
            );
            $participant->delete();
            return true;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    private static function ChangeParticipantAdmin(Thread $thread, Participant $participant, $admin = false)
    {
        try{
            $participant->admin = $admin ? 1 : 0;
            $participant->save();
            MessageService::StoreSystemMessage($thread, messenger_profile(), collect(["owner_id" => $participant->owner_id, "owner_type" => $participant->owner_type]), $admin ? 96 : 95);
            return $participant;
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    public static function KickParticipantGroup(Request $request, Thread $thread, Participant $participant)
    {
        if(ThreadService::IsLocked($thread, $participant) || !ThreadService::IsThreadAdmin($thread, $participant)){
            return [
                'state' => false,
                'error' => 'Access denied'
            ];
        }
        $remove = $thread->participants->firstWhere('id', $request->input('p_id'));
        if($remove && self::RemoveParticipant($thread, $remove, true)){
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

    public static function ModifyParticipantAdmin(Request $request, Thread $thread, Participant $participant, $admin)
    {
        if(ThreadService::IsLocked($thread, $participant) || !ThreadService::IsThreadAdmin($thread, $participant)){
            return [
                'state' => false,
                'error' => 'Access denied'
            ];
        }
        $target = self::LocateParticipantWithID($thread, $request->input('p_id'));
        $update = self::ChangeParticipantAdmin($thread, $target, $admin);
        if($target && $update){
            return [
                'state' => true,
                'data' => ($admin ? "You promoted ".$target->owner->name." to admin" : "You revoked admin from ".$target->owner->name)
            ];
        }
        return [
            'state' => false,
            'error' => 'Server Error'
        ];
    }

    public static function AddParticipantsGroupCheck(Request $request, Thread $thread, Participant $participant)
    {
        if(!ThreadService::CanAddParticipants($thread, $participant)){
            return [
                'state' => false,
                'error' => 'Access Denied'
            ];
        }
        $adding = self::AddParticipants($thread, $request);
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

    public static function LeaveGroupCheck(Thread $thread, Participant $participant)
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
                        self::RemoveParticipant($thread, $participant);
                        ThreadService::RemoveThread($thread);
                        break;
                    case 2:
                        $promote = self::ChangeParticipantAdmin($thread, $thread->participants->firstWhere('admin', 0), true);
                        if(!$promote){
                            return [
                                'state' => false,
                                'error' => 'Unable to promote other to admin'
                            ];
                        }
                        self::RemoveParticipant($thread, $participant);
                        break;
                    default:
                        if(ThreadService::IsLocked($thread, $participant)){
                            self::RemoveParticipant($thread, $participant);
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
                self::RemoveParticipant($thread, $participant);
            }
        }
        else{
            self::RemoveParticipant($thread, $participant);
        }
        return [
            'state' => true,
            'data' => 'You left the group '.$name
        ];
    }
}