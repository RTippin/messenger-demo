<?php

namespace App\Services;

use App\Http\Requests\Register;
use App\Models\Messages\Messenger;
use App\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;

class RegisterService
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function registerPost($mobile = false)
    {
        $validate = $this->validate($mobile);
        if(!$validate['state']){
            return $validate;
        }
        return $this->registerActions($mobile);
    }

    private function registerActions($mobile)
    {
        $newUser = $this->makeUser(['active' => 1]);
        if($newUser){
            if(self::makeUserMessenger($newUser)){
                if(!$mobile) Auth::guard()->login($newUser);
                return [
                    'state' => true,
                    'location' => false
                ];
            }
            try{
                $newUser->delete();
            }catch (Exception $e){
                report($e);
            }
        }
        return [
            'state' => false,
            'error' => 'Server Error'
        ];
    }

    private function makeUser(array $data)
    {
        try{
            $email = isset($data['email']) ? $data['email'] : $this->request->input('email');
            $newUser = new User();
            $newUser->first = $this->request->input('first');
            $newUser->last = $this->request->input('last');
            $newUser->email = $email;
            $newUser->password = bcrypt($this->request->input('password'));
            $newUser->active = $data['active'];
            $newUser->save();
            return $newUser;
        }catch (Exception $e){
            report($e);
            return null;
        }
    }

    private static function makeUserMessenger($user)
    {
        try{
            $messenger = new Messenger();
            $messenger->owner_id = $user->id;
            $messenger->owner_type = "App\User";
            $messenger->slug = Str::slug($user->last.' '.Str::random(4),'-').'_'.Carbon::now()->timestamp;
            $messenger->save();
            return true;
        }catch (Exception $e){
            report($e);
            return false;
        }
    }

    private function validate($mobile)
    {
        $validate = new Register($this->request);
        $validate = $validate->validate($mobile);
        if($validate->fails()){
            return array("state" => false, "error" => $validate->errors());
        }
        return array("state" => true);
    }
}
