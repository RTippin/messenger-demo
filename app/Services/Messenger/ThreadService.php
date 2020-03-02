<?php

namespace App\Services\Messenger;

use App\GhostUser;
use App\Models\Messages\Calls;
use App\Models\Messages\Participant;
use App\Services\Purge\MessagingPurge;
use App\Services\UploadService;
use App\Models\Messages\Thread;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use File;
use Validator;
use Cache;

class ThreadService
{
    public static function LocateThreads($type = 0, $with = null)
    {
        try{
            switch($type){
                case 0:
                    return messenger_profile()->threads()->with($with);
                break;
                case 1:
                    return messenger_profile()->threads()->where('ttype', 1)->with($with)->get();
                break;
                case 2:
                    return messenger_profile()->threads()->where('ttype', 2)->with($with)->get();
                break;
                case 3:
                    return messenger_profile()->threads()->limit(5)->with($with)->get();
                break;
            }
        }catch (Exception $e){
            report($e);
        }
        return null;
    }

    public static function LocateThread($id, $with = null)
    {
        $thread = $with ? Thread::with($with)->find($id) : Thread::find($id);
        if($thread){
            return $thread;
        }
        return null;
    }

    public static function RemoveThread(Thread $thread, $broadcast = false, $force = false)
    {
        try{
            if($force){
                $thread->forceDelete();
                return [
                    'state' => true
                ];
            }
            if(self::IsGroup($thread)){
                $name = $thread->name;
                MessageService::StoreSystemMessage($thread, messenger_profile(),'archived the group', 92, $broadcast);
                if($broadcast) (new BroadcastService($thread))->broadcastChannels()->broadcastThreadPurged();
                $thread->delete();
                return [
                    'state' => true,
                    'data' => 'You deleted the group conversation '.$name
                ];
            }
            if(self::IsPrivate($thread)){
                $party = self::OtherParty($thread, ParticipantService::LocateParticipant($thread));
                $name = 'Profile';
                if($party){
                    $name = $party->owner->name;
                    (new BroadcastService($thread))->broadcastKicked($party);
                }
                MessageService::StoreSystemMessage($thread, messenger_profile(),'archived the conversation', 92, false);
                $thread->delete();
                return [
                    'state' => true,
                    'data' => 'You removed the conversation between you and '.$name
                ];
            }
        }catch (Exception $e){
            report($e);
        }
        return [
            'state' => false
        ];
    }

    public static function AuthThreadSocket($id)
    {
        $thread = self::LocateThread($id, ['participants']);
        if($thread && $thread instanceof Thread){
            $participant = ParticipantService::LocateParticipant($thread);
            if($participant) return true;
        }
        return false;
    }

    public static function IsLocked(Thread $thread, Participant $participant)
    {
        if(self::IsPrivate($thread)){
            $party = self::OtherParty($thread, $participant);
            if($thread->lockout || $party->owner instanceof GhostUser) return true;
        }
        else if(self::IsGroup($thread) && $thread->lockout) return true;
        return false;
    }

    public static function IsGroup(Thread $thread)
    {
        return $thread->ttype === 2;
    }

    public static function IsPrivate(Thread $thread)
    {
        return $thread->ttype === 1;
    }

    public static function CanAddParticipants(Thread $thread, Participant $participant)
    {
        if(self::IsGroup($thread) && !self::IsLocked($thread, $participant) && (self::IsThreadAdmin($thread, $participant) || $thread->add_participants)){
            return true;
        }
        return false;
    }

    public static function CanSendMessage(Thread $thread, Participant $participant)
    {
        if(self::IsLocked($thread, $participant)
            || (self::IsGroup($thread) && (!$thread->send_message && !self::IsThreadAdmin($thread, $participant))))
        {
            return false;
        }
        return true;
    }

    public static function IsUnread(Thread $thread, Participant $participant)
    {
        if(!$participant || !$thread) return false;
        if ($participant->last_read === null || $thread->updated_at->gt($participant->last_read)) return true;
        return false;
    }

    public static function IsThreadAdmin(Thread $thread, Participant $participant)
    {
        return self::IsGroup($thread) ? $participant->admin === 1 : false;
    }

    public static function CanStartCall(Thread $thread, Participant $participant, $network = null)
    {
        if(self::IsLocked($thread, $participant)){
            return false;
        }
        if(self::IsGroup($thread)){
            if(self::IsThreadAdmin($thread, $participant) || $thread->admin_call){
                return true;
            }
            return false;
        }
        if(self::IsPrivate($thread)){
            $party = self::OtherParty($thread, $participant);
            if(!$party->owner->messenger->calls_outside_networks && (is_null($network) ? $participant->owner->networkStatus($party->owner) : $network) !== 1){
                return false;
            }
        }
        return true;
    }

    public static function CanEndCall(Thread $thread, Participant $participant, Calls $call)
    {
        if(self::IsThreadAdmin($thread, $participant) || $call->owner_id === $participant->owner_id){
            return true;
        }
        return false;
    }

    public static function ContactsFilterAdd(Thread $thread)
    {
        $participants = $thread->participants;
        messenger_profile()->load(['networks.party.messenger']);
        return messenger_profile()->networks->reject(function($net) use ($participants) {
            return $participants->firstWhere('owner_id', $net->party_id);
        });
    }

    public static function OtherParticipants(Thread $thread, Participant $participant)
    {
        if(!$participant || !$thread) return null;
        return $thread->participants->where('id', '!=', $participant->id);
    }

    public static function OtherParty(Thread $thread, Participant $participant, $party = null)
    {
        if($participant && $thread) $party = $thread->participants->firstWhere('owner_id', '!=', $participant->owner_id);
        return $party ? $party :  new Participant([
            'thread_id' => $thread->id
        ]);
    }

    public static function UnreadMessages(Thread $thread, Participant $participant)
    {
        //limit of 40 means will never show more than 40 unread count
        $messages = $thread->messages()->latest()->limit(40)->get();
        if(!$participant->last_read) return $messages;
        return $messages->filter(function ($message) use ($participant) {
            return $message->created_at->gt($participant->last_read);
        });
    }

    public static function RecentMessage(Thread $thread)
    {
        $message = $thread->relationLoaded('messages') ? $thread->messages->sortByDesc('created_at')->first() : $thread->latestMessage();
        if(!$message) return null;
        return $message;
    }

    public static function LastSeenMessage(Thread $thread, Participant $participant)
    {
        return $participant->last_read ? $thread->messages->sortByDesc('created_at')->where('created_at', '<=', $participant->last_read)->first() : null;
    }

    public static function LocateExistingPrivate($threads, $arr = ['check' => null])
    {
        try{
            $data = null;
            $alias = null;
            switch($arr['check']){
                case 'profile':
                    $class = get_alias_class($arr['alias']);
                    if($class){
                        $alias = $arr['alias'];
                        $data = $class::WhereHas('messenger', function($q) use($arr){
                            $q->where('slug', $arr['slug']);
                        })->first();
                    }
                break;
                case 'new_private':
                    $arg = explode('_', $arr['recipient']);
                    $class = get_alias_class($arg[0]);
                    if($class){
                        $alias = $arg[0];
                        $data = $class::find($arg[1]);
                    }
                break;
            }
            if(!$data){
                return [
                    'state' => false,
                    'error' => 'Profile not found'
                ];
            }
            foreach($threads as $thread){
                if($thread->participants->contains('owner_id', $data->id)){
                    return [
                        'state' => true,
                        'thread_id' => $thread->id,
                        'the_thread' => $thread
                    ];
                }
            }
            return [
                'state' => false,
                'model' => $data,
                'type' => $alias
            ];
        }catch (Exception $e){
            report($e);
            return [
                'state' => false,
                'error' => 'Server Error'
            ];
        }
    }

    private static function StoreThread(Int $type, $attr = [])
    {
        try{
            $thread = new Thread();
            $thread->subject = isset($attr['subject']) ? $attr['subject'] : null;
            $thread->image = isset($attr['image']) ? $attr['image'] : null;
            $thread->ttype = $type;
            $thread->save();
            return $thread;
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    public static function StorePrivateThread(Request $request)
    {
        $check = self::LocateExistingPrivate(
            self::LocateThreads(1, ['participants']),
            ['check' => 'new_private', 'recipient' => $request->input('recipient')]
        );
        if($check['state'] || isset($check['error'])){
            return [
                'state' => false,
                'error' => isset($check['error']) ? $check['error'] : 'You have an existing conversation with that profile'
            ];
        }
        $thread = self::StoreThread(1);
        if($thread){
            $myself = ParticipantService::StoreParticipant($thread);
            $other = ParticipantService::StoreParticipant($thread, $check['model']);
            $message = MessageService::StoreNewMessage($request, $thread, $myself);
            if($myself && $other && $message['state']){
                return [
                    'state' => true,
                    'thread_id' => $thread->id
                ];
            }
            $thread->forceDelete();
        }
        return [
            'state' => false,
            'error' => 'Unable to complete your request'
        ];
    }

    public static function StoreGroupThread(Request $request)
    {
        $validator = Validator::make($request->all(), ['subject' => 'required|max:50']);
        if($validator->fails()){
            return [
                'state' => false,
                'error' => $validator->errors()->first()
            ];
        }
        $thread = self::StoreThread(2, [
            'subject' => $request->input('subject'),
            'image' => rand(1,5).'.png'
        ]);
        if($thread){
            $myself = ParticipantService::StoreParticipant($thread, null, true);
            MessageService::StoreSystemMessage($thread, messenger_profile(),'created '.$thread->name, 93, false);
            ParticipantService::AddParticipants($thread, $request);
            if($myself){
                return [
                    'state' => true,
                    'thread_id' => $thread->id
                ];
            }
            $thread->forceDelete();
        }
        return [
            'state' => false,
            'error' => 'Unable to complete your request'
        ];
    }

    public static function StoreGroupSettings(Request $request, Thread $thread, Participant $participant)
    {
        if(self::IsLocked($thread, $participant) || !self::IsThreadAdmin($thread, $participant)){
            return [
                'state' => false,
                'error' => 'Access denied'
            ];
        }
        $validator = Validator::make($request->all(),
            [
                'subject' => 'required|max:50',
                'add_participant' => 'required|boolean',
                'admin_call' => 'required|boolean',
                'send_message' => 'required|boolean'
            ]
        );
        if($validator->fails()){
            return [
                'state' => false,
                'error' => $validator->errors()
            ];
        }
        $original_name = $thread->name;
        $thread->timestamps = false;
        $thread->subject = strip_tags($request->input('subject'));
        $thread->add_participants = $request->input('add_participant');
        $thread->admin_call = $request->input('admin_call');
        $thread->send_message = $request->input('send_message');
        $thread->save();
        if($original_name !== $thread->name) MessageService::StoreSystemMessage($thread, messenger_profile(),'renamed the group to '.$thread->name, 94);
        return [
            'state' => true,
            'data' => $thread->name
        ];
    }

    public static function CheckArchiveThread(Thread $thread, Participant $participant)
    {
        if(CallService::LocateActiveCall($thread)){
            return [
                'state' => false,
                'error' => 'Unable to proceed, please end the active call first'
            ];
        }
        if(self::IsPrivate($thread)){
            $party = self::OtherParty($thread, $participant);
            return [
                'state' => true,
                'data' => [
                    'name' => $party ? $party->owner->name : 'Profile',
                    'messages' => $thread->messages->count(),
                    'participants' => null,
                    'type' => 1
                ]
            ];
        }
        if(self::IsGroup($thread) && !self::IsLocked($thread, $participant) && self::IsThreadAdmin($thread, $participant)){
            return [
                'state' => true,
                'data' => [
                    'name' => $thread->name,
                    'messages' => $thread->messages->count(),
                    'participants' => $thread->participants->count(),
                    'type' => 2
                ]
            ];
        }
        return [
            'state' => false,
            'error' => 'Access Denied'
        ];
    }

    public static function ProcessArchiveThread(Thread $thread, Participant $participant)
    {
        $check = self::CheckArchiveThread($thread, $participant);
        if(!$check['state']){
            return [
                'state' => false,
                'error' => 'Access Denied'
            ];
        }
        $remove = self::RemoveThread($thread, true);
        if($remove['state']){
            return [
                'state' => true,
                'data' => $remove['data']
            ];
        }
        return [
            'state' => false,
            'error' => 'Server Error'
        ];
    }

    public static function UpdateGroupAvatar(Request $request, Thread $thread, Participant $participant)
    {
        if(self::IsLocked($thread, $participant) || !self::IsThreadAdmin($thread, $participant)){
            return [
                'state' => false,
                'error' => 'Access Denied'
            ];
        }
        try{
            switch($request->input('action')){
                case 'default':
                    $validator = Validator::make($request->all(),['avatar' => 'required|between:1,5']);
                    if($validator->fails()){
                        return array("state" => false, "error" => "Invalid default avatar");
                    }
                    if(!in_array($thread->image, array('1.png','2.png','3.png','4.png','5.png'), true )){
                        $file_path = storage_path('app/public/messenger/avatar/'.$thread->image);
                        if(file_exists($file_path)){
                            File::delete($file_path);
                        }
                    }
                    $thread->image = $request->input('avatar').'.png';
                    $thread->save();
                    MessageService::StoreSystemMessage($thread, messenger_profile(),'updated the groups avatar', 91, true);
                    return [
                        'state' => true,
                        'data' => [
                            'message' => $thread->name.' \'s avatar was updated',
                            'avatar' => $thread->avatar
                        ]
                    ];
                    break;
                case 'upload':
                    $upload = (new UploadService($request))->newUpload('group_avatar');
                    if($upload["state"]){
                        $old = $thread->image;
                        if(!in_array($old, array('1.png','2.png','3.png','4.png','5.png'), true )){
                            $file_path = storage_path('app/public/messenger/avatar/'.$old);
                            if(file_exists($file_path)){
                                File::delete($file_path);
                            }
                        }
                        $thread->image = $upload["text"];
                        $thread->save();
                        MessageService::StoreSystemMessage($thread, messenger_profile(),'updated the groups avatar', 91, true);
                        return [
                            'state' => true,
                            'data' => [
                                'message' => $thread->name.' \'s avatar was updated',
                                'avatar' => $thread->avatar
                            ]
                        ];
                    }
                    return [
                        'state' => false,
                        'error' => $upload['error']
                    ];
                    break;
            }
        }catch (Exception $e){
            report($e);
        }
        return [
            'state' => false,
            'error' => 'Server Error'
        ];
    }

    public static function SendKnock(Thread $thread, Participant $participant, $force = false)
    {
        if(self::IsLocked($thread, $participant)){
            return [
                'state' => false,
                'error' => 'Access Denied'
            ];
        }
        if(self::IsThreadAdmin($thread, $participant)){
            if(Cache::has('sent_knok_'.$thread->id) && !$force){
                return [
                    'state' => false,
                    'error' => 'You may only knock at '.$thread->name.' once every five minutes'
                ];
            }
            Cache::put('sent_knok_'.$thread->id, true, Carbon::now()->addMinutes(5));
            (new BroadcastService($thread))->broadcastChannels($force, true)->broadcastGroupKnok();
            return [
                'state' => true,
                'data' => $thread->name
            ];
        }
        if(self::IsPrivate($thread)){
            $party = self::OtherParty($thread, $participant);
            if(!$party->owner->messenger->knoks){
                return [
                    'state' => false,
                    'error' => $party->owner->name.' is not accepting knocks at this time'
                ];
            }
            if(Cache::has('sent_knok_'.$thread->id.'_'.$party->owner_id) && !$force){
                return [
                    'state' => false,
                    'error' => 'You may only knock at '.$party->owner->name.' once every five minutes'
                ];
            }
            Cache::put('sent_knok_'.$thread->id.'_'.$party->owner_id, true, Carbon::now()->addMinutes(5));
            (new BroadcastService($thread))->broadcastKnok($party);
            return [
                'state' => true,
                'data' => $party->owner->name
            ];
        }
        return [
            'state' => false,
            'error' => 'Access Denied'
        ];
    }

    public static function PurgeArchivedThreads($days = 90)
    {
        $threads = Thread::onlyTrashed()->get();
        foreach($threads as $thread){
            if($thread->deleted_at->addDays($days) <= Carbon::now()){
                (new MessagingPurge($thread))->startDelete('thread');
            }
        }
    }
}
