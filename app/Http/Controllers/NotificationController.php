<?php
namespace App\Http\Controllers;

use App\GhostUser;
use Illuminate\Http\Request;
use View;

class NotificationController extends Controller
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    public function deleteNotifications()
    {
        $allCount = messenger_profile()->notifications->count();
        if($this->request->input('notify_count') < $allCount || $allCount === 0){
            return $this->pullNotifications();
        }
        messenger_profile()->notifications()->delete();
        return response()->json(['state' => false, 'count' => 0], 200);
    }

    public function locateModel($class, $id)
    {
        $user = class_exists($class) ? $class::find($id) : null;
        return $user ? $user : new GhostUser();
    }


    public function pullNotifications()
    {
        $allCount = messenger_profile()->notifications->count();
        if($this->request->input('notify_count') >= $allCount || $allCount === 0){
            return response()->json(['state' => false, 'count' => $allCount], 200);
        }
        $view = '';
        foreach(messenger_profile()->notifications as $notification){
            switch ($notification->type){
                case 'App\Notifications\NetworksAdd':
                    $model = $this->locateModel($notification->data['owner_type'], $notification->data['owner_id']);
                    $data['image'] = $model->avatar();
                    $data['name'] = $model->name;
                    $data['msg'] = ($notification->data['action'] ? 'You must approve or deny the request' : 'You approved the request');
                    $data['created_at'] = $notification->created_at;
                    $data['read_at'] = $notification->read_at;
                    $view .= View::make('notifications.user.NetworkAction', compact('data'))->render();
                break;
                case 'App\Notifications\NetworksAccept':
                    $model = $this->locateModel($notification->data['owner_type'], $notification->data['owner_id']);
                    $data['image'] = $model->avatar();
                    $data['name'] = $model->name;
                    $data['created_at'] = $notification->created_at;
                    $data['read_at'] = $notification->read_at;
                    $view .= View::make('notifications.user.NetworksAccept', compact('data'))->render();
                break;
            }
        }
        messenger_profile()->unreadNotifications->markAsRead();
        return response()->json(['count' => $allCount, 'html' => $view, 'state' => true], 200);
    }

}
