<?php

namespace App\Services;

use App\Http\Requests\Register;
use App\Models\Messages\MessengerSettings;
use App\User;
use Auth;
use App\Models\User\UserInfo;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Exception;

class RegisterService extends Service
{

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
            if(self::makeUserInfo($newUser) && self::makeUserMessengerSettings($newUser)){
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
            $newUser->firstName = $this->request->input('firstName');
            $newUser->lastName = $this->request->input('lastName');
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

    private static function makeUserInfo($user)
    {
        try{
            $info = new UserInfo();
            $info->user_id = $user->id;
            $info->slug = Str::slug($user->lastName.' '.Str::random(4),'-').'_'.Carbon::now()->timestamp;
            $info->save();
            return true;
        }catch (Exception $e){
            report($e);
            return false;
        }
    }

    private static function makeUserMessengerSettings($user)
    {
        try{
            $messageSettings = new MessengerSettings();
            $messageSettings->owner_id = $user->id;
            $messageSettings->owner_type = "App\User";
            $messageSettings->save();
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
