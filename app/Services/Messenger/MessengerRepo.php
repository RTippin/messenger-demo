<?php

namespace App\Services\Messenger;

use App\Models\Messages\Calls;
use App\Models\Messages\GroupInviteLink;
use App\Models\Messages\Participant;
use App\Models\Messages\Thread;
use App\Models\Messages\Message;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class MessengerRepo
{
    public static function MakeMessenger()
    {
        $settings = messenger_profile()->messenger;
        return [
            'message_popups' => $settings->message_popups,
            'message_sound' => $settings->message_sound,
            'call_ringtone_sound' => $settings->call_ringtone_sound,
            'notify_sound' => $settings->notify_sound,
            'knoks' => $settings->knoks,
            'calls_outside_networks' => $settings->calls_outside_networks,
            'friend_approval' => $settings->friend_approval,
            'dark_mode' => $settings->dark_mode,
            'online_status' => $settings->online_status
        ];
    }

    public static function MakeProfileThreads()
    {
        $threads = collect([]);
        try{
            $_threads = ThreadService::LocateThreads(0, ['participants.owner.messenger', 'activeCall', 'calls.participants.owner']);
            if($_threads){
                $_threads->each(function($thread) use($threads){
                    $construct = self::MakeThread($thread);
                    if($construct) $threads->push($construct);
                });
            }
        }catch (Exception $e){
            report($e);
        }
        return $threads;
    }

    public static function MakeProfileRecentThreads()
    {
        $threads = collect([]);
        try{
            $_threads = ThreadService::LocateThreads(3, ['participants.owner.messenger', 'activeCall', 'calls.participants.owner']);
            if($_threads){
                $_threads->each(function($thread) use($threads){
                    $construct = self::MakeThread($thread);
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
                'thread_type' => $thread->ttype,
                'created_at' => $call->created_at->toDateTimeString(),
                'locale_created_at' => format_date_timezone($call->created_at)->toDateTimeString()
            ];
        }catch (Exception $e){
            report($e);
        }
        return collect([]);
    }

    public static function MakeActiveCalls()
    {
        $calls = collect([]);
        try{
            messenger_profile()->ongoingCalls->each(function($thread) use($calls){
                $party = ThreadService::OtherParty($thread, ParticipantService::LocateParticipant($thread));
                $calls->push([
                    'call_id' => $thread->activeCall->id,
                    'call_type' => $thread->activeCall->type,
                    'call_mode' => $thread->activeCall->mode,
                    'thread_id' => $thread->id,
                    'thread_type' => $thread->ttype,
                    'name' => ThreadService::IsGroup($thread) ? $thread->name : $party->owner->name,
                    'avatar' => ThreadService::IsGroup($thread) ? $thread->avatar : $party->owner->avatar,
                    'in_call' => CallService::IsInCall($thread->activeCall),
                    'left_call' => CallService::HasLeftCall($thread->activeCall)
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
            'expires' => $link->expires_at ? $link->expires_at->toDateTimeString() : null,
            'created_at' => $link->created_at->toDateTimeString(),
            'locale_created_at' => format_date_timezone($link->created_at)->toDateTimeString()
        ];
    }

    public static function MakeGroupParticipants(Collection $group_participants)
    {
        $participants = collect([]);
        try{
            $group_participants->each(function($participant) use($participants){
                $participants->push([
                    'id' => $participant->id,
                    'owner_id' => $participant->owner->id,
                    'name' => $participant->owner->name,
                    'slug' => $participant->owner->slug(false),
                    'route' => $participant->owner->slug(true),
                    'avatar' => $participant->owner->avatar(),
                    'online' => $participant->owner->isOnline(),
                    'type' => get_messenger_alias($participant->owner),
                    'network' => messenger_profile()->networkStatus($participant->owner),
                    'admin' => $participant->admin,
                    'created_at' => $participant->created_at->toDateTimeString(),
                    'locale_created_at' => format_date_timezone($participant->created_at)->toDateTimeString()
                ]);
            });
        }catch (Exception $e){
            report($e);
        }
        return $participants;
    }

    public static function MakeMessage(Thread $thread, Message $message, $temp = null)
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
                'extra' => null,
                'created_at' => $message->created_at->toDateTimeString(),
                'locale_created_at' => format_date_timezone($message->created_at)->toDateTimeString(),
                'temp_id' => $temp
            ];
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    public static function MakeThread(Thread $thread)
    {
        try{
            $participant = ParticipantService::LocateParticipant($thread);
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
                'locale_created_at' => format_date_timezone($thread->created_at)->toDateTimeString(),
                'locale_updated_at' => format_date_timezone($thread->updated_at)->toDateTimeString(),
                'recent_message' => [
                    'message_type' => $messageLatest ? $messageLatest->mtype : 0,
                    'name' => $messageLatest ? $messageLatest->owner->name : 'No',
                    'body' => $messageLatest ? MessageService::MessageContentsFormat($thread, $messageLatest) : 'Messages'
                ],
                'call' => [
                    'status' => $call ? true : false,
                    'call_id' => $call ? $call->id : null,
                    'call_type' => $call ? $call->type : null,
                    'in_call' => $call ? CallService::IsInCall($call) : false,
                    'left_call' => $call ? CallService::HasLeftCall($call) : false,
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

    public static function MakeBobbleHeads(Thread $thread)
    {
        $bobble_heads = collect([]);
        try{
            $thread->participants->reject(function ($value){
                return $value->owner_id === messenger_profile()->id;
            })->each(function($participant) use($bobble_heads, $thread){
                $last_message = ThreadService::LastSeenMessage($thread, $participant);
                $bobble_heads->push([
                    'last_active' => ($participant->owner->messenger->online_status === 0 ? null : $participant->owner->messenger->updated_at->toDateTimeString()),
                    'locale_last_active' => ($participant->owner->messenger->online_status === 0 ? null : format_date_timezone($participant->owner->messenger->updated_at)->toDateTimeString()),
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

    public static function MakePrivateParty(Thread $thread, Participant $participant, $party)
    {
        try{
            $network = messenger_profile()->networkStatus($party->owner);
            return [
                'owner_id' => $party->owner->id,
                'slug' => $party->owner->slug(false),
                'route' => $party->owner->slug(true),
                'type' => get_messenger_alias($party->owner),
                'network' => $network,
                'can_call' => ThreadService::CanStartCall($thread, $participant, $network),
                'knoks' => $party->owner->messenger->knoks
            ];
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    public static function MakePrivateThread(Thread $thread)
    {
        if(ThreadService::IsPrivate($thread)){
            $participant = ParticipantService::LocateParticipant($thread);
            $party = ThreadService::OtherParty($thread, $participant);
            $thread_gen = self::MakeThread($thread);
            if(!$thread_gen){
                return null;
            }
            return [
                'bobble_heads' => self::MakeBobbleHeads($thread),
                'party' => self::MakePrivateParty($thread, $participant, $party),
                'thread' => $thread_gen,
                'recent_messages' => self::MakeThreadMessages($thread, MessageService::PullMessagesMethod($thread))
            ];
        }
        return null;
    }

    public static function MakeGroupThread(Thread $thread)
    {
        if(ThreadService::IsGroup($thread)){
            return [
                'bobble_heads' => self::MakeBobbleHeads($thread),
                'thread' => self::MakeThread($thread),
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
