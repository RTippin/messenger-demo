<?php

namespace App\Services\Messenger;

use App\Models\Messages\Calls;
use App\Models\Messages\GroupInviteLink;
use App\Models\Messages\Participant;
use App\Models\Messages\Thread;
use App\Models\Messages\Message;
use Exception;

class MessengerRepo
{
    public static function MakeMessengerSettings($model)
    {
        $settings = $model->messengerSettings;
        return [
            'message_popups' => $settings->message_popups,
            'message_sound' => $settings->message_sound,
            'call_ringtone_sound' => $settings->call_ringtone_sound,
            'knoks' => $settings->knoks,
            'calls_outside_networks' => $settings->calls_outside_networks,
            'online_status' => $settings->online_status
        ];
    }

    public static function MakeProfileThreads($model)
    {
        $threads = collect([]);
        try{
            $_threads = ThreadService::LocateThreads($model, 0, ['participants.owner.messengerSettings', 'participants.owner.info', 'activeCall', 'messages.owner', 'calls.participants.owner']);
            if($_threads){
                $_threads->each(function($thread) use($threads, $model){
                    $construct = self::MakeThread($thread, $model);
                    if($construct) $threads->push($construct);
                });
            }
        }catch (Exception $e){
            report($e);
        }
        return $threads;
    }

    public static function MakeCall(Thread $thread, Calls $call)
    {
        try{
            return [
                'call_id' => $call->id,
                'call_type' => $call->type,
                'call_mode' => $call->mode,
                'thread_id' => $thread->id,
                'thread_type' => $thread->ttype
            ];
        }catch (Exception $e){
            report($e);
        }
        return collect([]);
    }

    public static function MakeActiveCalls($model)
    {
        $calls = collect([]);
        try{
            $model->ongoingCalls->each(function($thread) use($calls, $model){
                $party = ThreadService::OtherParty($thread, ParticipantService::LocateParticipant($thread, $model));
                $calls->push([
                    'call_id' => $thread->activeCall->id,
                    'call_type' => $thread->activeCall->type,
                    'call_mode' => $thread->activeCall->mode,
                    'thread_id' => $thread->id,
                    'thread_type' => $thread->ttype,
                    'name' => ThreadService::IsGroup($thread) ? $thread->name : $party->owner->name,
                    'avatar' => ThreadService::IsGroup($thread) ? $thread->avatar : $party->owner->avatar,
                    'in_call' => CallService::IsInCall($thread->activeCall, $model),
                    'left_call' => CallService::HasLeftCall($thread->activeCall, $model)
                ]);
            });
        }catch (Exception $e){
            report($e);
        }
        return $calls;
    }

    public static function MakeThreadMessages(Thread $thread, $messages)
    {
        $collection = collect([]);
        try{
            if($messages && count($messages)){
                $messages->each(function($message) use($collection, $thread){
                    $construct = self::MakeMessage($thread, $message);
                    if($construct) $collection->push($construct);
                });
            }
        }catch (Exception $e){
            report($e);
        }
        return $collection;
    }

    public static function MakeGroupInvite(GroupInviteLink $link)
    {
        return [
            'id' => $link->id,
            'slug' => route('messenger_invite_join', $link->slug),
            'max_use' => $link->max_use,
            'uses' => $link->uses,
            'expires' => $link->expires_at ? $link->expires_at->diffForHumans() : null
        ];
    }

    public static function MakeMessage(Thread $thread, Message $message)
    {
        try{
            return [
                'message_id' => $message->id,
                'thread_id' => $message->thread_id,
                'owner_id' => $message->owner_id,
                'owner_type' => $message->owner_type,
                'message_type' => $message->mtype,
                'name' => $message->owner->name,
                'avatar' => $message->owner->avatar,
                'slug' => $message->owner->slug(true),
                'body' => MessageService::MessageContentsFormat($thread, $message),
                'created_at' => $message->created_at->toDateTimeString()
            ];
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    public static function MakeThread(Thread $thread, $model)
    {
        try{
            $participant = ParticipantService::LocateParticipant($thread, $model);
            $messageLatest = ThreadService::RecentMessage($thread);
            $party = ThreadService::OtherParty($thread, $participant);
            $unread = ThreadService::IsUnread($thread, $participant);
            $call = CallService::LocateActiveCall($thread);
            return [
                'thread_id' => $thread->id,
                'thread_type' => $thread->ttype,
                'unread' => $unread,
                'unread_count' => $unread ? ThreadService::UnreadMessages($thread, $participant)->count() : 0,
                'name' => ThreadService::IsGroup($thread) ? $thread->name : $party->owner->name,
                'avatar' => ThreadService::IsGroup($thread) ? $thread->avatar : $party->owner->avatar,
                'online' => ThreadService::IsGroup($thread) ? 0 : $party->owner->onlineStatusNumber,
                'created_at' => $thread->created_at->toDateTimeString(),
                'updated_at' => $thread->updated_at->toDateTimeString(),
                'recent_message' => [
                    'message_type' => $messageLatest ? $messageLatest->mtype : 0,
                    'name' => $messageLatest ? $messageLatest->owner->name : 'No',
                    'body' => $messageLatest ? MessageService::MessageContentsFormat($thread, $messageLatest) : 'Messages'
                ],
                'call' => [
                    'status' => $call ? true : false,
                    'call_id' => $call ? $call->id : null,
                    'call_type' => $call ? $call->type : null,
                    'in_call' => $call ? CallService::IsInCall($call, $model) : false,
                    'left_call' => $call ? CallService::HasLeftCall($call, $model) : false,
                ],
                'options' => [
                    'admin' => ThreadService::IsThreadAdmin($thread, $participant),
                    'add_participants' => $thread->add_participants,
                    'admin_call' => $thread->admin_call,
                    'send_message' => $thread->send_message,
                    'lockout' => ThreadService::IsLocked($thread, $participant)
                ]
            ];
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    public static function MakeBobbleHeads(Thread $thread, $model)
    {
        $bobble_heads = collect([]);
        try{
            $thread->participants->reject(function ($value) use($model){
                return $value->owner_id === $model->id;
            })->each(function($participant) use($bobble_heads, $thread){
                $last_message = ThreadService::LastSeenMessage($thread, $participant);
                $bobble_heads->push([
                    'last_active' => ($participant->owner->messengerSettings->online_status === 0 ? null : $participant->owner->messengerSettings->updated_at->toDateTimeString()),
                    'owner_id' => $participant->owner_id,
                    'owner_type' => $participant->owner_type,
                    'name' => $participant->owner->name,
                    'avatar' => $participant->owner->avatar,
                    'typing' => false,
                    'caught_up' => false,
                    'in_chat' => false,
                    'online' => $participant->owner->isOnline(),
                    'message_id' => $last_message ? $last_message->id : null
                ]);
            });
        }catch (Exception $e){
            report($e);
        }
        return $bobble_heads;
    }

    public static function MakePrivateParty(Thread $thread, Participant $participant, $model, $party)
    {
        try{
            $network = $model->networkStatus($party->owner);
            return [
                'owner_id' => $party->owner->id,
                'slug' => $party->owner->slug(false),
                'route' => $party->owner->slug(true),
                'type' => strtolower(class_basename($party->owner)),
                'network' => $network,
                'can_call' => ThreadService::CanStartCall($thread, $participant, $network),
                'knoks' => $party->owner->messengerSettings->knoks
            ];
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    public static function MakePrivateThread(Thread $thread, $model)
    {
        if(ThreadService::IsPrivate($thread)){
            $participant = ParticipantService::LocateParticipant($thread, $model);
            $party = ThreadService::OtherParty($thread, $participant);
            $thread_gen = self::MakeThread($thread, $model);
            if(!$thread_gen){
                return null;
            }
            return [
                'bobble_heads' => self::MakeBobbleHeads($thread, $model),
                'party' => self::MakePrivateParty($thread, $participant, $model, $party),
                'thread' => $thread_gen,
                'recent_messages' => self::MakeThreadMessages($thread, MessageService::PullMessagesMethod($thread))
            ];
        }
        return null;
    }

    public static function MakeGroupThread(Thread $thread, $model)
    {
        if(ThreadService::IsGroup($thread)){
            return [
                'bobble_heads' => self::MakeBobbleHeads($thread, $model),
                'thread' => self::MakeThread($thread, $model),
                'recent_messages' => self::MakeThreadMessages($thread, MessageService::PullMessagesMethod($thread))
            ];
        }
        return null;
    }

    public static function MakeGroupSettings(Thread $thread)
    {
        try{
            return [
                'thread_id' => $thread->id,
                'name' => $thread->name,
                'avatar' => $thread->avatar,
                'participant_count' => $thread->participants->count(),
                'add_participants' => $thread->add_participants,
                'start_calls' => $thread->admin_call,
                'send_messages' => $thread->send_message
            ];
        }catch (Exception $e){
            report($e);
            return null;
        }
    }
}
