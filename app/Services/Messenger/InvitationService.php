<?php

namespace App\Services\Messenger;

use App\Models\Messages\GroupInviteLink;
use App\Models\Messages\Participant;
use App\Models\Messages\Thread;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Exception;

class InvitationService
{
    public static function ValidateInviteLink($invite)
    {
        if(!isset($invite)
            || (!$invite instanceof GroupInviteLink)
            || !$invite->thread
            || ($invite->thread && $invite->thread->locked)
            || ($invite->expires_at && $invite->expires_at <= Carbon::now())
            || ($invite->max_use > 0 && $invite->uses >= $invite->max_use)
        ){
            if(isset($invite) && $invite instanceof GroupInviteLink) self::RemoveGroupInvite($invite);
            return false;
        }
        return true;
    }

    private static function RemoveGroupInvite(GroupInviteLink $inviteLink)
    {
        try{
            $inviteLink->delete();
            return true;
        }catch (Exception $e){
            report($e);
            return false;
        }
    }

    private static function StoreGroupInvite(Thread $thread, $attr = [])
    {
        try{
            $invite = new GroupInviteLink();
            $invite->thread_id = $thread->id;
            $invite->owner_id = messenger_profile()->id;
            $invite->owner_type = get_class(messenger_profile());
            $invite->max_use = $attr['max'];
            $invite->uses = 0;
            $invite->expires_at = $attr['expires'];
            $invite->save();
            return $invite;
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    public static function GenerateGroupInvitation(Request $request, Thread $thread, Participant $participant)
    {
        if(ThreadService::IsLocked($thread, $participant) || !ThreadService::IsThreadAdmin($thread, $participant)){
            return [
                'state' => false,
                'error' => 'Access denied'
            ];
        }
        $validator = Validator::make($request->all(),
            [
                'expires' => 'required|between:1,6',
                'uses' => 'required|between:1,7'
            ]
        );
        if($validator->fails()){
            return [
                'state' => false,
                'error' => $validator->errors()
            ];
        }
        $max = 0;
        $expires = null;
        $time = Carbon::now();
        switch ($request->input('expires')){
            case 1:
                $expires = $time->addMinutes(30);
            break;
            case 2:
                $expires = $time->addHour();
            break;
            case 3:
                $expires = $time->addHours(6);
            break;
            case 4:
                $expires = $time->addHours(12);
            break;
            case 5:
                $expires = $time->addDay();
            break;
        }
        switch ($request->input('uses')){
            case 2:
                $max = 1;
            break;
            case 3:
                $max = 5;
            break;
            case 4:
                $max = 10;
            break;
            case 5:
                $max = 25;
            break;
            case 6:
                $max = 50;
            break;
            case 7:
                $max = 100;
            break;
        }
        if($thread->groupInviteLink) self::RemoveGroupInvite($thread->groupInviteLink);
        $invite = self::StoreGroupInvite($thread, [
            'max' => $max,
            'expires' => $expires
        ]);
        if($invite){
            return [
                'state' => true,
                'data' => [
                    "has_invite" => true,
                    "invite" => MessengerRepo::MakeGroupInvite($invite)
                ]
            ];
        }
        return [
            'state' => false,
            'error' => 'Server Error'
        ];
    }

    public static function DestroyGroupInvitation(Request $request, Thread $thread, Participant $participant)
    {
        if(ThreadService::IsLocked($thread, $participant) || !ThreadService::IsThreadAdmin($thread, $participant)){
            return [
                'state' => false,
                'error' => 'Access denied'
            ];
        }
        $invite = $thread->groupInviteLink;
        if($invite
            && $invite->id === $request->input('invite_id')
            && self::RemoveGroupInvite($invite)
        ){
            return [
                'state' => true
            ];
        }
        return [
            'state' => false,
            'error' => 'Unable to complete your request'
        ];
    }

    public static function CanJoinWithInvite(Request $request)
    {
        $invite = GroupInviteLink::where(DB::raw('BINARY `slug`'), $request->slug)->with('thread.participants.owner')->first();
        if(!$invite || !self::ValidateInviteLink($invite)){
            return [
                'state' => false,
                'error' => 'Invalid invite link'
            ];
        }
        if(messenger_profile()){
            if(ParticipantService::LocateParticipant($invite->thread)){
                return [
                    'state' => true,
                    'data' => [
                        'invite' => $invite,
                        'can_join' => false
                    ]
                ];
            }
            return [
                'state' => true,
                'data' => [
                    'invite' => $invite,
                    'can_join' => true
                ]
            ];
        }
        return [
            'state' => true,
            'data' => [
                'invite' => $invite,
                'can_join' => false
            ]
        ];
    }

    private static function UpdateInvitationUse(GroupInviteLink $inviteLink)
    {
        try{
            $inviteLink->uses = $inviteLink->uses+1;
            $inviteLink->save();
            self::ValidateInviteLink($inviteLink);
        }catch (Exception $e){
            report($e);
        }
    }

    public static function JoinParticipantWithInvite(Request $request)
    {
        $check = self::CanJoinWithInvite($request);
        if(!$check['state']){
            return [
                'state' => false,
                'error' => $check['error']
            ];
        }
        if(!$check['data']['can_join']){
            return [
                'state' => false,
                'error' => 'You already belong to this group'
            ];
        }
        $thread = $check['data']['invite']->thread;
        if(ParticipantService::StoreOrRestoreParticipant($thread)){
            MessageService::StoreSystemMessage($thread, messenger_profile(), 'joined the group', 88);
            self::UpdateInvitationUse($check['data']['invite']);
            return [
                'state' => true
            ];
        }
        return [
            'state' => false,
            'error' => 'Server Error'
        ];
    }

    public static function ValidateAllInvites()
    {
        GroupInviteLink::all()->each(function ($invite){
            self::ValidateInviteLink($invite);
        });
    }
}