<?php

namespace App\Http\Controllers;

use App\Services\Messenger\MessengerRepo;
use App\Services\Messenger\MessengerService;
use App\Services\Messenger\ThreadService;
use Illuminate\Http\Request;


class MessagesController extends Controller
{
    protected $messenger, $request;
    public function __construct(Request $request, MessengerService $messenger)
    {
        $this->request = $request;
        $this->messenger = $messenger;
    }

    public function index()
    {
        return view('messenger.portal')->with('mode', 5);
    }

    public function fetch()
    {
        switch($this->request->type){
            case 'settings':
                return response()->json(MessengerRepo::MakeMessenger());
            break;
            case 'threads':
                return response()->json([
                    'threads' => MessengerRepo::MakeProfileThreads()
                ]);
            break;
            case 'recent_threads':
                return response()->json([
                    'recent_threads' => MessengerRepo::MakeProfileRecentThreads()
                ]);
            break;
            case 'load_thread':
                $data = $this->messenger->routeRequest('load_thread');
                if($data['state']){
                    return response()->json($data['data']);
                }
            break;
            case 'unread_count':
                return response()->json([
                    'total_unread' => messenger_profile()->unreadThreadsCount()
                ]);
            break;
            case 'is_unread':
                $data = $this->messenger->routeRequest('is_unread');
                if($data['state']){
                    return response()->json(['unread' => $data['data']]);
                }
                return response()->json(['unread' => false]);
            break;
            case 'initiate_thread':
                $data = $this->messenger->routeRequest('initiate_thread', false);
                if($data['state']){
                    return response()->json($data['data']);
                }
            break;
            case 'load_private':
                $data = $this->messenger->routeRequest('load_private');
                if($data['state']){
                    return response()->json($data['data']);
                }
            break;
            case 'load_group':
                $data = $this->messenger->routeRequest('load_group');
                if($data['state']){
                    return response()->json($data['data']);
                }
            break;
            case 'bobble_heads':
                $data = $this->messenger->routeRequest('bobble_heads');
                if($data['state']){
                    return response()->json([
                        'bobble_heads' => $data['data']
                    ]);
                }
            break;
            case 'thread_logs':
                $data = $this->messenger->routeRequest('thread_logs');
                if($data['state']){
                    return response()->json([
                        'messages' => $data['data']
                    ]);
                }
            break;
            case 'recent_messages':
            case 'init_messages':
                $data = $this->messenger->routeRequest('recent_messages');
                if($data['state']){
                    return response()->json([
                        "messages" => $data['data']
                    ]);
                }
            break;
            case 'messages':
                $data = $this->messenger->routeRequest('messages');
                if($data['state']){
                    return response()->json([
                        "messages" => $data['data']
                    ]);
                }
            break;
            case 'participants':
                $data = $this->messenger->routeRequest('participants');
                if($data['state']){
                    return response()->json([
                        'participants' => $data['data']['participants'],
                        'admin' => $data['data']['admin']
                    ]);
                }
            break;
            case 'add_participants':
                $data = $this->messenger->routeRequest('add_participants');
                if($data['state']){
                    return response()->json([
                        'friends' => $data['data']
                    ]);
                }
            break;
            case 'group_settings':
                $data = $this->messenger->routeRequest('group_settings');
                if($data['state']){
                    return response()->json($data['data']);
                }
            break;
            case 'group_invites':
                $data = $this->messenger->routeRequest('group_invite');
                if($data['state']){
                    return response()->json($data['data']);
                }
            break;
            case 'mark_read':
                $data = $this->messenger->routeRequest('mark_read');
                if($data['state']){
                    return response()->json(['status' => 1]);
                }
            break;
            case 'archive_thread':
                $data = $this->messenger->routeRequest('archive_thread');
                if($data['state']){
                    return response()->json($data['data']);
                }
            break;
        }
        if(isset($data) && isset($data['error'])){
            return response()->json(['errors' => ['forms' => $data['error']]], 400);
        }
        return response()->json(['errors' => ['forms' => 'Error gathering the data you requested']], 400);
    }

    public function update()
    {
        switch($this->request->input('type')){
            case 'settings':
                $dispatch = $this->messenger->routeCreate('store_messenger_settings', false);
                if($dispatch["state"]){
                    return response()->json($dispatch['data']);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'store_message':
                $dispatch = $this->messenger->routeCreate('store_message');
                if($dispatch['state']){
                    return response()->json([
                        'message' => $dispatch['data']
                    ]);
                }
                return response()->json(['errors' => ['forms' => $dispatch['error']]], 400);
            break;
            case 'store_messenger_avatar':
                $dispatch = $this->messenger->routeCreate('store_messenger_avatar', false);
                if($dispatch["state"]){
                    return response()->json(['avatar' => $dispatch['data']]);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'remove_messenger_avatar':
                $dispatch = $this->messenger->routeDestroy('remove_messenger_avatar', false);
                if($dispatch["state"]){
                    return response()->json(['avatar' => $dispatch['data']]);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'new_private':
                $dispatch = $this->messenger->routeCreate('store_private', false);
                if($dispatch["state"]){
                    return response()->json(['thread_id' => $dispatch['data']]);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'new_group':
                $dispatch = $this->messenger->routeCreate('store_group', false);
                if($dispatch["state"]){
                    return response()->json(['thread_id' => $dispatch["data"]]);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'leave_group':
                $dispatch = $this->messenger->routeDestroy('leave_group');
                if($dispatch["state"]){
                    return response()->json(['msg' => $dispatch['data']]);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'add_group_participants':
                $dispatch = $this->messenger->routeCreate('add_group_participants');
                if($dispatch["state"]){
                    return response()->json(['names' => $dispatch['data']['names'], 'subject' => $dispatch['data']['subject']]);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'admin_remove_participant':
                $dispatch = $this->messenger->routeDestroy('admin_remove_participant');
                if($dispatch["state"]){
                    return response()->json(['msg' => $dispatch['data'], 'status' => 1]);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'admin_group_settings':
                $dispatch = $this->messenger->routeCreate('admin_group_settings');
                if($dispatch["state"]){
                    return response()->json(['subject' => $dispatch['data']]);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'store_group_invitation':
                $dispatch = $this->messenger->routeCreate('store_group_invitation');
                if($dispatch["state"]){
                    return response()->json($dispatch['data']);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'remove_group_invitation':
                $dispatch = $this->messenger->routeDestroy('remove_group_invitation');
                if($dispatch["state"]){
                    return response()->json(['status' => 1]);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'remove_message':
                $dispatch = $this->messenger->routeDestroy('remove_message');
                if($dispatch["state"]){
                    return response()->json(['status' => 1]);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'participant_admin_revoke':
                $dispatch = $this->messenger->routeDestroy('participant_admin_revoke');
                if($dispatch["state"]){
                    return response()->json(['msg' => $dispatch['data'], 'admin' => false]);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'participant_admin_grant':
                $dispatch = $this->messenger->routeCreate('participant_admin_grant');
                if($dispatch["state"]){
                    return response()->json(['msg' => $dispatch['data'], 'admin' => true]);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'archive_thread':
                $dispatch = $this->messenger->routeDestroy('archive_thread');
                if($dispatch["state"]){
                    return response()->json(['msg' => $dispatch['data']]);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'store_avatar':
                $dispatch = $this->messenger->routeCreate('store_avatar');
                if($dispatch["state"]){
                    return response()->json(['msg' => $dispatch['data']['message'], 'avatar' => $dispatch['data']['avatar']]);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'send_knock':
                $dispatch = $this->messenger->routeCreate('send_knock');
                if($dispatch["state"]){
                    return response()->json(['name' => $dispatch['data']]);
                }
                return response()->json(['errors' => ['forms' => $dispatch["error"]]], 400);
            break;
            case 'initiate_call':
                $dispatch = $this->messenger->routeCreate('initiate_call');
                if($dispatch['state']){
                    return response()->json($dispatch['data'], 200);
                }
                return response()->json(['errors' => ['forms' => $dispatch['error']]], 400);
            break;
            case 'join_call':
                $dispatch = $this->messenger->routeCreate('join_call');
                if($dispatch['state']){
                    return response()->json($dispatch['data'], 200);
                }
                return response()->json(['errors' => ['forms' => $dispatch['error']]], 400);
            break;
            case 'leave_call':
                $dispatch = $this->messenger->routeDestroy('leave_call');
                if($dispatch['state']){
                    return response()->json($dispatch['data'], 200);
                }
                return response()->json(['errors' => ['forms' => $dispatch['error']]], 400);
            break;
            case 'end_call':
                $dispatch = $this->messenger->routeDestroy('end_call');
                if($dispatch['state']){
                    return response()->json($dispatch['data'], 200);
                }
                return response()->json(['errors' => ['forms' => $dispatch['error']]], 400);
            break;
        }
        return response()->json(['errors' => ['forms' => 'Error gathering the data you requested']], 400);
    }

    public function showThread()
    {
        return view('messenger.portal')->with(['mode' => 0, 'thread_id' => $this->request->thread_id]);
    }

    public function viewCreatePrivate()
    {
        return view('messenger.portal')->with('slug', $this->request->slug)->with('type', $this->request->alias)->with('mode', 3);
    }

    public function checkCreatePrivate()
    {
        $threads = ThreadService::LocateThreads(1, ['participants']);
        $check = ThreadService::LocateExistingPrivate($threads, ['check' => 'profile', 'alias' => $this->request->alias, 'slug' => $this->request->slug]);
        if($check['state']){
            return response()->json([
                'exist' => true,
                'thread_id' => $check['thread_id']
            ]);
        }
        if(isset($check['error'])){
            return response()->json(['errors' => ['forms' => $check['error']]], 400);
        }
        return response()->json([
            'exist' => false,
            'party' => [
                'owner_id' => $check['model']->id,
                'avatar' => $check['model']->avatar,
                'name' => $check['model']->name,
                'type' => $check['type'],
                'online' => $check['model']->isOnline(),
                'slug' => $check['model']->slug(false),
                'route' => $check['model']->slug(true),
                'network' => messenger_profile()->networkStatus($check['model']),
            ]
        ]);
    }

    public function openCall()
    {
        if(!config('messenger.calls')){
            return response()->view('errors.custom', ['err' => 'callError'], 403);
        }
        $dispatch = $this->messenger->routeRequest('view_call');
        if($dispatch['state']){
            return view('messenger.video')->with('thread', $dispatch['data']['thread'])->with('call', $dispatch['data']['call'])->with('call_admin', $dispatch['data']['call_admin']);
        }
        return response()->view('errors.custom', ['err' => 'callError'], 403);
    }

    public function callFetch()
    {
        switch($this->request->type){
            case 'heartbeat':
                $dispatch = $this->messenger->routeRequest('call_heartbeat');
                if($dispatch['state']){
                    return response()->json(['status' => 1], 200);
                }
                return response()->json(['errors' => ['forms' => $dispatch['error']]], 400);
            break;
        }
        return response()->json(['errors' => ['forms' => "Error gathering the data you requested"]], 400);
    }

    public function joinInviteLink()
    {
        if($this->request->isMethod('post')){
            $dispatch = $this->messenger->routeCreate('invitation_join', false);
            if($dispatch['state']){
                return response()->json(['status' => 1], 200);
            }
            return response()->json(['errors' => ['forms' => $dispatch['error']]], 400);
        }
        $dispatch = $this->messenger->routeRequest('invitation_join', false);
        if($dispatch['state']){
            return view('messenger.invitation')->with('invite', $dispatch['data']['invite'])->with('special_flow', true)->with('can_join', $dispatch['data']['can_join']);
        }
        return response()->view('errors.custom', ['err' => 'badJoinLink'], 404);
    }

}
