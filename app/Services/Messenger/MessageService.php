<?php

namespace App\Services\Messenger;

use App\GhostUser;
use App\Models\Messages\Message;
use App\Models\Messages\Participant;
use App\Services\Purge\MessagingPurge;
use App\Services\UploadService;
use App\Models\Messages\Thread;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use LaravelJoyPixels;

class MessageService
{
    public static function GetMessageFromThread(Thread $thread, $id)
    {
        return $thread->messages()->find($id);
    }

    public static function LocateGlobalMessageById($id)
    {
        return Message::with(['thread'])->find($id);
    }

    public static function PullMessagesMethod(Thread $thread, $limit = 40, $arr = [])
    {
        if(count($arr)){
            switch($arr['type']){
                case 'history':
                    $message = self::GetMessageFromThread($thread, $arr['message_id']);
                    if(!$message) return null;
                    return $thread->messages()->latest()->with('owner.messenger')->where('created_at', '<=', $message->created_at)->where('id', '!=', $message->id)->limit($limit)->get()->reverse();
                break;
                case 'logs':
                    return $thread->messages()->with('owner.messenger')->whereNotIn('mtype', [0,1,2])->get();
                break;
            }
        }
        return $thread->messages->sortByDesc('created_at')->take($limit)->reverse();
    }

    public static function MessageContentsFormat(Thread $thread, Message $message)
    {
        if(!self::isSystemMessage($message)) return htmlspecialchars($message->body);
        $data = json_decode($message->body, true);
        switch($message->mtype){
            case 90: //video call
                $call = $thread->calls->firstWhere('id', $data['call_id']);
                if($call){
                    $names = '';
                    $participants = $call->participants->reject(function($value) use($message){
                        return $value->owner_id === $message->owner_id;
                    });
                    if($participants->count()){
                        foreach($participants as $participant){
                            $names .= $participant->owner->name.', ';
                        }
                        return 'was in a video call with '.rtrim($names,', ');
                    }
                }
                return 'was in a video call';
            break;
            case 88: //participant joined with invite link
            case 91: //group avatar updated
            case 92: //group archived
            case 93: //created group
            case 94: //renamed group
            case 97: //participant left group
                return $message->body;
            break;
            case 95: //removed admin from participant
                return 'revoked administrator from '.self::LocateContentModel($data, $thread)->name;
                break;
            case 96: //made participant admin
                return 'promoted '.self::LocateContentModel($data, $thread)->name.' to administrator';
                break;
            case 98: //removed participant from group
                return 'removed '.self::LocateContentModel($data, $thread)->name.' from the group';
                break;
            case 99: //added participant to group
                $names = 'added ';
                foreach($data as $profile){
                    $names .= self::LocateContentModel($profile, $thread)->name.', ';
                }
                return rtrim($names,', ').' to the group';
                break;
            default : return 'system message';
        }
    }

    public static function isSystemMessage(Message $message)
    {
        return !in_array($message->mtype, [0,1,2]);
    }

    public static function LocateContentModel($data, Thread $thread)
    {
        if($thread && $thread instanceof Thread && $thread->participants){
            $participant = $thread->participants->firstWhere('owner_id', $data['owner_id']);
            if($participant && $participant->owner) return $participant->owner;
        }
        $model = class_exists($data['owner_type']) ? $data['owner_type']::find($data['owner_id']) : new GhostUser();
        return $model ? $model : new GhostUser();
    }

    private static function FormatMessageType(Request $request)
    {
        if(($request->file('doc_file'))){
            return [
                'state' => true,
                'type' => 2,
                'data' => $request->file('doc_file')
            ];
        }
        if(($request->file('image_file'))){
            return [
                'state' => true,
                'type' => 1,
                'data' => $request->file('image_file')
            ];
        }
        if($request->input('message')){
            return [
                'state' => true,
                'type' => 0,
                'data' => $request->input('message')
            ];
        }
        return [
            'state' => false,
            'error' => 'No input found'
        ];
    }

    private static function MessageFormatText($body)
    {
        if(empty($body)){
            return [
                'state' => false,
                'error' => 'Message is empty'
            ];
        }
        return [
            'state' => true,
            'text' => LaravelJoyPixels::toShort($body)
        ];
    }

    private static function RemoveMessage(Message $message)
    {
        try{
            $message->setTouchedRelations([]);
            $message->delete();
            return true;
        }catch (Exception $e){
            report($e);
            return false;
        }
    }

    private static function StoreMessage($arr = [])
    {
        try{
            $message = new Message();
            $message->thread_id = $arr['thread_id'];
            $message->body = $arr['body'];
            $message->owner_id = $arr['owner_id'];
            $message->owner_type = $arr['owner_type'];
            $message->mtype = $arr['mtype'];
            $message->save();
            return $message;
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    public static function StoreNewMessage(Request $request, Thread $thread, Participant $participant)
    {
        if(!ThreadService::CanSendMessage($thread, $participant)){
            return [
                'state' => false,
                'error' => 'You do not have permission to message right now'
            ];
        }
        $contents = self::FormatMessageType($request);
        if(!$contents['state']){
            return [
                'state' => false,
                'error' => $contents['error']
            ];
        }
        switch($contents['type']){
            case 0:
                $body = self::MessageFormatText($contents['data']);
                break;
            case 1:
                $body = (new UploadService($request))->newUpload('message_photo');
                break;
            case 2:
                $body = (new UploadService($request))->newUpload('message_doc');
                break;
            default: $body = ['state' => false, 'error' => 'Invalid type'];
        }
        if(!$body['state']){
            return [
                'state' => false,
                'error' => $body['error']
            ];
        }
        $message = self::StoreMessage([
            'thread_id' => $thread->id,
            'body' => $body['text'],
            'owner_id' => messenger_profile()->id,
            'owner_type' => get_class(messenger_profile()),
            'mtype' => $contents['type']
        ]);
        if($message && $message instanceof Message){
            (new BroadcastService($thread))->broadcastChannels(true)->broadcastMessage($message->load('owner.messenger'), $request->input('temp_id'));
            return [
                'state' => true,
                'data' => $message
            ];
        }
        return [
            'state' => false,
            'error' => 'Failed to store message'
        ];
    }

    public static function StoreSystemMessage(Thread $thread, $model, $body, $type, $broadcast = true)
    {
        try{
            $message = self::StoreMessage([
                'thread_id' => $thread->id,
                'body' => $body,
                'owner_id' => $model->id,
                'owner_type' => get_class($model),
                'mtype' => $type
            ]);
            if($broadcast){
                (new BroadcastService($thread))->broadcastChannels(true)->broadcastMessage($message->load('owner.messenger'));
            }
        }catch (Exception $e){
            report($e);
        }
    }

    public static function DestroyMessageCheck(Request $request, Thread $thread, Participant $participant)
    {
        $message = self::GetMessageFromThread($thread, $request->input('message_id'));
        if(!$message
            || ThreadService::IsLocked($thread, $participant)
            || self::isSystemMessage($message)
            || (ThreadService::IsPrivate($thread) && !messenger_profile()->is($message->owner))
            || (ThreadService::IsGroup($thread) && !messenger_profile()->is($message->owner) && !ThreadService::IsThreadAdmin($thread, $participant))
        ){
            return [
                'state' => false,
                'error' => 'Access denied'
            ];
        }
        (new BroadcastService($thread))->broadcastChannels()->broadcastMessagePurged($message);
        if(self::RemoveMessage($message)){
            return [
                'state' => true
            ];
        }
        return [
            'state' => false,
            'error' => 'Server Error'
        ];
    }

    public static function PurgeArchivedMessages($days = 90)
    {
        $messages = Message::onlyTrashed()->get();
        foreach($messages as $message){
            if($message->deleted_at->addDays($days) <= Carbon::now()){
                (new MessagingPurge($message))->startDelete('message');
            }
        }
    }
}
