<?php
namespace App\Http\Controllers;

use App\GhostUser;
use App\User;
use View;

class NotificationController extends Controller
{
    public function pullNotifications()
    {
        return $this->pullNotificationsUser();
    }

    public function deleteNotifications()
    {
        $allCount = $this->modelType()->notifications->count();
        if($this->request->input('notify_count') < $allCount || $allCount === 0){
            return $this->pullNotifications();
        }
        $this->modelType()->notifications()->delete();
        return response()->json(['state' => false, 'count' => 0], 200);
    }

    public function locateUser($all, $id)
    {
        $user = $all->find($id);
        return $user ? $user : new GhostUser();
    }


    public function pullNotificationsUser()
    {
        $allCount = $this->auth->notifications->count();
        if($this->request->input('notify_count') >= $allCount || $allCount === 0){
            return response()->json(['state' => false, 'count' => $allCount], 200);
        }
        $users = User::all();
        $view = '';
        foreach($this->auth->notifications as $notification){
            switch ($notification->type){
                case 'App\Notifications\NetworksAdd':
                    $model = $this->locateUser($users, $notification->data['owner_id']);
                    $data['image'] = $model->avatar();
                    $data['name'] = $model->name;
                    $data['msg'] = ($notification->data['action'] ? 'You must approve or deny the request' : 'You approved the request');
                    $data['created_at'] = $notification->created_at;
                    $data['read_at'] = $notification->read_at;
                    $view .= View::make('notifications.user.NetworkAction', compact('data'))->render();
                break;
                case 'App\Notifications\NetworksAccept':
                    $model = $this->locateUser($users, $notification->data['owner_id']);
                    $data['image'] = $model->avatar();
                    $data['name'] = $model->name;
                    $data['created_at'] = $notification->created_at;
                    $data['read_at'] = $notification->read_at;
                    $view .= View::make('notifications.user.NetworksAccept', compact('data'))->render();
                break;
            }
        }
        $this->auth->unreadNotifications->markAsRead();
        return response()->json(['count' => $allCount, 'html' => $view, 'state' => true], 200);
    }

}
