<?php
namespace App\Http\Controllers;

use App\Models\Messages\Messenger;

class ProfileController extends Controller
{
    public function viewProfile($alias, $slug, $message = null)
    {
        if($message === 'message'){
            return redirect()->route('messages.create', ['slug' => $slug, 'alias' => $alias]);
        }
        $profile = Messenger::query()->where('slug', $slug)->with(['owner.networks.party'])->first();
        if(!$profile || ($profile && get_messenger_alias($profile->owner) !== $alias)){
            return response()->view('errors.custom', ['err' => 'noProfile'], 404);
        }
        $con = 0;
        if(messenger_profile()){
            $con = messenger_profile()->networkStatus($profile->owner);
        }
        return view('profiles.base')->with(compact('profile','con'));
    }
}
