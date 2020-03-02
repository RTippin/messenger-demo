<?php

namespace App\Services\Messenger;

use App\Models\Messages\Thread;
use App\Services\Social\NetworksService;
use App\Services\UploadService;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Validator;
use Exception;
use File;

class MessengerService
{

    protected $request, $thread, $participant, $message, $call;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function authorize($thread = null, $load = null)
    {
        if($thread instanceof Thread){
            $_thread = ($load ? $thread->load($load) : $thread);
        }
        else if($thread){
            $_thread = ThreadService::LocateThread($thread, $load);
        }
        else{
            $_thread = ThreadService::LocateThread($this->request->thread_id, $load);
        }
        if($_thread && $_thread instanceof Thread){
            $participant = ParticipantService::LocateParticipant($_thread);
            if($participant){
                $this->thread = $_thread;
                $this->participant = $participant;
                return [
                    "state" => true,
                    "participant" => $participant,
                    "thread" => $_thread
                ];
            }
        }
        return [
            "state" => false
        ];
    }

    public function routeRequest($type, $auth = true)
    {
        $data = null;
        $error = null;
        $type_loads = [
            'load_thread' => ['participants.owner.messenger', 'activeCall', 'calls'],
            'load_private' => [
                'participants',
                'activeCall',
                'calls.participants.owner',
                'calls',
                'messages' => function(HasMany $query){
                    $query->latest()->limit(40)->with('owner.messenger');
                }
            ],
            'load_group' => [
                'participants.owner.messenger',
                'activeCall',
                'calls.participants.owner',
                'calls',
                'messages' => function(HasMany $query){
                    $query->latest()->limit(40)->with('owner.messenger');
                }
            ],
            'recent_messages' => [
                'participants.owner',
                'calls.participants.owner',
                'calls',
                'messages' => function(HasMany $query){
                    $query->latest()->limit(40)->with('owner.messenger');
                }
            ],
            'bobble_heads' => ['participants.owner.messenger'],
            'messages' => ['participants.owner', 'calls.participants.owner', 'calls'],
            'thread_logs' => ['calls.participants.owner', 'calls'],
            'participants' => ['participants.owner.messenger'],
            'add_participants' => ['participants.owner.messenger'],
            'group_settings' => ['participants'],
            'mark_read' => ['participants'],
            'group_invite' => ['groupInviteLink.thread'],
            'check_archive_thread' => ['participants.owner.messenger', 'messages'],
            'view_call' => ['participants.owner.messenger', 'activeCall']
        ];
        $authorize = $auth ? $this->authorize(null, (isset($type_loads[$type]) ? $type_loads[$type] : null)) : ['state' => true];
        if($authorize['state']) {
            switch ($type){
                case 'initiate_thread':
                    $thread = ThreadService::LocateThread($this->request->thread_id, null);
                    if($thread){
                        if(ThreadService::IsPrivate($thread) && $this->authorize($thread, $type_loads['load_private'])['state']){
                            ParticipantService::MarkRead($this->participant);
                            $data = MessengerRepo::MakePrivateThread($this->thread);
                        }
                        if(ThreadService::IsGroup($thread) && $this->authorize($thread, $type_loads['load_group'])['state']){
                            ParticipantService::MarkRead($this->participant);
                            $data = MessengerRepo::MakeGroupThread($this->thread);
                        }
                    }
                    $error = 'Unable to locate that conversation';
                break;
                case 'load_thread':
                    $data = MessengerRepo::MakeThread($this->thread);
                break;
                case 'load_private':
                    ParticipantService::MarkRead($this->participant);
                    $data = MessengerRepo::MakePrivateThread($this->thread);
                break;
                case 'is_unread':
                    $data = ThreadService::IsUnread($this->thread, $this->participant);
                break;
                case 'load_group':
                    ParticipantService::MarkRead($this->participant);
                    $data = MessengerRepo::MakeGroupThread($this->thread);
                break;
                case 'mark_read':
                    $data = ParticipantService::MarkRead($this->participant);
                break;
                case 'bobble_heads':
                    $data = MessengerRepo::MakeBobbleHeads($this->thread);
                break;
                case 'recent_messages':
                    $data = MessengerRepo::MakeThreadMessages($this->thread, MessageService::PullMessagesMethod($this->thread));
                break;
                case 'messages':
                    $data = MessengerRepo::MakeThreadMessages($this->thread, MessageService::PullMessagesMethod($this->thread, 25, ['type' => 'history', 'message_id' => $this->request->message_id]));
                break;
                case 'thread_logs':
                    $data = MessengerRepo::MakeThreadMessages($this->thread, MessageService::PullMessagesMethod($this->thread, null, ['type' => 'logs']));
                break;
                case 'participants':
                    $error = "Permission denied";
                    if(ThreadService::IsGroup($this->thread)){
                        $data = [
                            "participants" => MessengerRepo::MakeGroupParticipants(ThreadService::OtherParticipants($this->thread, $this->participant)),
                            "admin" => ThreadService::IsThreadAdmin($this->thread, $this->participant)
                        ];
                    }
                break;
                case 'add_participants':
                    $error = "Permission denied";
                    if(ThreadService::CanAddParticipants($this->thread, $this->participant)){
                        $data = NetworksService::MakeFriendsFiltered(ThreadService::ContactsFilterAdd($this->thread));
                    }
                break;
                case 'group_settings':
                    $error = "Permission denied";
                    if(ThreadService::IsThreadAdmin($this->thread, $this->participant)){
                        $data = MessengerRepo::MakeGroupSettings($this->thread);
                    }
                break;
                case 'group_invite':
                    $error = "Permission denied";
                    if(ThreadService::IsThreadAdmin($this->thread, $this->participant)){
                        $verify = InvitationService::ValidateInviteLink($this->thread->groupInviteLink);
                        $data = [
                            "has_invite" => $verify,
                            "invite" => $verify ? MessengerRepo::MakeGroupInvite($this->thread->groupInviteLink) : null
                        ];
                    }
                break;
                case 'archive_thread':
                    $check = ThreadService::CheckArchiveThread($this->thread, $this->participant);
                    if($check['state']){
                        $data = $check['data'];
                    }
                    else $error = $check['error'];
                break;
                case 'invitation_join':
                    $check = InvitationService::CanJoinWithInvite($this->request);
                    if($check['state']){
                        $data = $check['data'];
                    }
                    else $error = $check['error'];
                break;
                case 'view_call':
                    $view = CallService::ViewCall($this->request, $this->thread, $this->participant);
                    if($view['state']){
                        $data = $view['data'];
                    }
                    else $error = $view['error'];
                break;
                case 'call_heartbeat':
                    $heartbeat = CallService::CallHeartbeat($this->request, $this->thread);
                    if($heartbeat['state']){
                        $data = $heartbeat['data'];
                    }
                    else $error = $heartbeat['error'];
                break;
            }
            if($data){
                return [
                    'state' => true,
                    'data' => $data
                ];
            }
        }
        return [
            'state' => false,
            'error' => $error ? $error : "Unable to authorize your request"
        ];
    }

    public function routeCreate($type, $auth = true)
    {
        $data = null;
        $error = null;
        $type_loads = [
            'store_message' => ['participants.owner.devices'],
            'leave_group' => ['participants.owner.devices'],
            'add_group_participants' => ['participants'],
            'admin_group_settings' => ['participants'],
            'store_group_invitation' => ['participants', 'groupInviteLink'],
            'participant_admin_grant' => ['participants'],
            'send_knock' => ['participants.owner.devices', 'participants.owner.messenger'],
            'initiate_call' => ['participants.owner', 'activeCall'],
            'join_call' => ['participants.owner', 'activeCall.participants'],
        ];
        $authorize = $auth ? $this->authorize(null, (isset($type_loads[$type]) ? $type_loads[$type] : null)) : ['state' => true];
        if($authorize['state']) {
            switch ($type) {
                case 'send_knock':
                    $knock = ThreadService::SendKnock($this->thread, $this->participant);
                    if($knock['state']){
                        $data = $knock['data'];
                    }
                    else $error = $knock['error'];
                break;
                case 'store_messenger_settings':
                    $settings = self::StoreMessenger($this->request);
                    if($settings['state']){
                        $data = MessengerRepo::MakeMessenger();
                    }
                    else $error = $settings['error'];
                break;
                case 'store_messenger_avatar':
                    $avatar = self::StoreMessengerAvatar($this->request);
                    if($avatar['state']){
                        $data = $avatar['data'];
                    }
                    else $error = $avatar['error'];
                break;
                case 'store_private':
                    $thread = ThreadService::StorePrivateThread($this->request);
                    if($thread['state']){
                        $data = $thread['thread_id'];
                    }
                    else $error = $thread['error'];
                break;
                case 'store_group':
                    $thread = ThreadService::StoreGroupThread($this->request);
                    if($thread['state']){
                        $data = $thread['thread_id'];
                    }
                    else $error = $thread['error'];
                break;
                case 'store_message':
                    $message = MessageService::StoreNewMessage($this->request, $this->thread, $this->participant);
                    if($message['state']){
                        ParticipantService::MarkRead($this->participant);
                        $data = MessengerRepo::MakeMessage($this->thread, $message['data'], $this->request->input('temp_id'));
                    }
                    else $error = $message['error'];
                break;
                case 'add_group_participants':
                    $adding = ParticipantService::AddParticipantsGroupCheck($this->request, $this->thread, $this->participant);
                    if($adding['state']){
                        $data = $adding['data'];
                    }
                    else $error = $adding['error'];
                break;
                case 'admin_group_settings':
                    $settings = ThreadService::StoreGroupSettings($this->request, $this->thread, $this->participant);
                    if($settings['state']){
                        $data = $settings['data'];
                    }
                    else $error = $settings['error'];
                break;
                case 'store_group_invitation':
                    $invitation = InvitationService::GenerateGroupInvitation($this->request, $this->thread, $this->participant);
                    if($invitation['state']){
                        $data = $invitation['data'];
                    }
                    else $error = $invitation['error'];
                break;
                case 'participant_admin_grant':
                    $action = ParticipantService::ModifyParticipantAdmin($this->request, $this->thread, $this->participant, true);
                    if($action['state']){
                        $data = $action['data'];
                    }
                    else $error = $action['error'];
                break;
                case 'store_avatar':
                    $avatar = ThreadService::UpdateGroupAvatar($this->request, $this->thread, $this->participant);
                    if($avatar['state']){
                        $data = $avatar['data'];
                    }
                    else $error = $avatar['error'];
                break;
                case 'invitation_join':
                    $join = InvitationService::JoinParticipantWithInvite($this->request);
                    if($join['state']){
                        $data = true;
                    }
                    else $error = $join['error'];
                break;
                case 'initiate_call':
                    $call = CallService::StartNewCall($this->thread, $this->participant);
                    if($call['state']){
                        $data = $call['data'];
                    }
                    else $error = $call['error'];
                break;
                case 'join_call':
                    $call = CallService::JoinCall($this->thread);
                    if($call['state']){
                        $data = $call['data'];
                    }
                    else $error = $call['error'];
                break;
            }
            if($data){
                return [
                    'state' => true,
                    'data' => $data
                ];
            }
        }
        return [
            'state' => false,
            'error' => $error ? $error : "Unable to authorize your request"
        ];
    }

    public function routeDestroy($type, $auth = true)
    {
        $data = null;
        $error = null;
        $type_loads = [
            'leave_group' => ['participants.owner.devices'],
            'admin_remove_participant' => ['participants.owner.devices'],
            'remove_group_invitation' => ['participants', 'groupInviteLink'],
            'remove_message' => ['participants'],
            'participant_admin_revoke' => ['participants'],
            'leave_call' => ['activeCall.participants'],
            'end_call' => ['participants.owner', 'activeCall.participants']
        ];
        $authorize = $auth ? $this->authorize(null, (isset($type_loads[$type]) ? $type_loads[$type] : null)) : ['state' => true];
        if($authorize['state']) {
            switch ($type) {
                case 'remove_messenger_avatar':
                    $avatar = self::RemoveMessengerAvatar();
                    if($avatar['state']){
                        $data = $avatar['data'];
                    }
                    else $error = $avatar['error'];
                break;
                case 'leave_group':
                    $leaving = ParticipantService::LeaveGroupCheck($this->thread, $this->participant);
                    if($leaving['state']){
                        $data = $leaving['data'];
                    }
                    else $error = $leaving['error'];
                break;
                case 'admin_remove_participant':
                    $kicking = ParticipantService::KickParticipantGroup($this->request, $this->thread, $this->participant);
                    if($kicking['state']){
                        $data = $kicking['data'];
                    }
                    else $error = $kicking['error'];
                break;
                case 'remove_group_invitation':
                    $removing = InvitationService::DestroyGroupInvitation($this->request, $this->thread, $this->participant);
                    if($removing['state']){
                        $data = true;
                    }
                    else $error = $removing['error'];
                break;
                case 'remove_message':
                    $message = MessageService::DestroyMessageCheck($this->request, $this->thread, $this->participant);
                    if($message['state']){
                        $data = true;
                    }
                    else $error = $message['error'];
                break;
                case 'participant_admin_revoke':
                    $action = ParticipantService::ModifyParticipantAdmin($this->request, $this->thread, $this->participant, false);
                    if($action['state']){
                        $data = $action['data'];
                    }
                    else $error = $action['error'];
                break;
                case 'archive_thread':
                    $thread = ThreadService::ProcessArchiveThread($this->thread, $this->participant);
                    if($thread['state']){
                        $data = $thread['data'];
                    }
                    else $error = $thread['error'];
                break;
                case 'leave_call':
                    $call = CallService::LeaveCall($this->thread);
                    if($call['state']){
                        $data = $call['data'];
                    }
                    else $error = $call['error'];
                break;
                case 'end_call':
                    $call = CallService::EndCall($this->thread, $this->participant);
                    if($call['state']){
                        $data = $call['data'];
                    }
                    else $error = $call['error'];
                break;
            }
            if($data){
                return [
                    'state' => true,
                    'data' => $data
                ];
            }
        }
        return [
            'state' => false,
            'error' => $error ? $error : "Unable to authorize your request"
        ];
    }

    private static function StoreMessengerAvatar(Request $request)
    {
        if(!config('messenger.avatar_upload')){
            return [
                'state' => false,
                'error' => 'Avatar upload is currently disabled'
            ];
        }
        try{
            $dispatch = new UploadService($request);
            $dispatch = $dispatch->newUpload('messenger_avatar');
            if(!$dispatch['state']){
                return [
                    'state' => false,
                    'error' => $dispatch['error']
                ];
            }
            messenger_profile()->messenger->picture = $dispatch['text'];
            messenger_profile()->messenger->save();
            return [
                'state' => true,
                'data' => messenger_profile()->avatar
            ];
        }catch (Exception $e){
            report($e);
            return [
                'state' => false,
                'error' => 'Unable to update your avatar'
            ];
        }
    }

    private static function RemoveMessengerAvatar()
    {
        if(!config('messenger.avatar_removal')){
            return [
                'state' => false,
                'error' => 'Avatar removal is currently disabled'
            ];
        }
        try{
            $old = messenger_profile()->messenger->picture;
            $file_path = storage_path('app/public/profile/'.messenger_alias().'/'.$old);
            if(file_exists($file_path)){
                File::delete($file_path);
            }
            messenger_profile()->messenger->picture = null;
            messenger_profile()->messenger->save();
            return [
                "state" => true,
                "data" => messenger_profile()->avatar
            ];
        }catch (Exception $e){
            report($e);
            return [
                'state' => false,
                'error' => 'Unable to update your avatar'
            ];
        }
    }

    private static function StoreMessenger(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'message_popups' => 'required|boolean',
                'message_sound' => 'required|boolean',
                'call_ringtone_sound' => 'required|boolean',
                'notify_sound' => 'required|boolean',
                'knoks' => 'required|boolean',
                'calls_outside_networks' => 'required|boolean',
                'friend_approval' => 'required|boolean',
                'dark_mode' => 'required|boolean',
                'online_status' => 'required|between:0,2'
            ]
        );
        if($validator->fails()){
            return [
                'state' => false,
                'error' => 'Unable to save your settings'
            ];
        }
        try{
            messenger_profile()->messenger->message_popups = $request->input('message_popups');
            messenger_profile()->messenger->message_sound = $request->input('message_sound');
            messenger_profile()->messenger->call_ringtone_sound = $request->input('call_ringtone_sound');
            messenger_profile()->messenger->notify_sound = $request->input('notify_sound');
            messenger_profile()->messenger->knoks = $request->input('knoks');
            messenger_profile()->messenger->calls_outside_networks = $request->input('calls_outside_networks');
            messenger_profile()->messenger->friend_approval = $request->input('friend_approval');
            messenger_profile()->messenger->dark_mode = $request->input('dark_mode');
            messenger_profile()->messenger->online_status = $request->input('online_status');
            messenger_profile()->messenger->save();
            return [
                'state' => true,
                'model' => messenger_profile()
            ];
        }catch (Exception $e){
            report($e);
            return [
                'state' => false,
                'error' => 'Server Error'
            ];
        }
    }

}
