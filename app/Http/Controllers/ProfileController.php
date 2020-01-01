<?php
namespace App\Http\Controllers;

use App\User;

class ProfileController extends Controller
{

    public function viewUserProfile($slug, $redirect = null)
    {
        if($redirect === 'message'){
            return redirect()->route('messages.create', ['slug' => $slug, 'type' => 'user']);
        }
        $userViewing = User::query()->whereHas('info', function ($q) use($slug){
            $q->where('slug', $slug);
        })->with(['networks.party.info'])->first();
        if(!$userViewing){
            return response()->view('errors.custom', ['err' => 'noProfile'], 404);
        }
        $con = 0;
        if($this->modelType()){
            $con = $this->modelType()->networkStatus($userViewing);
        }
        return view('profiles.user.base')->with(compact('userViewing','con'));
    }
}
