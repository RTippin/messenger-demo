<?php

namespace App\Http\Middleware;

use Closure;

class SetMessengerModel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     * Perform your logic here to determine which model you
     * want to be used throughout the application,
     * as this messenger supports multiple models
     */
    public function handle($request, Closure $next)
    {
//        //Example. Write logic so we know what model you want to use
//        if(auth('company')->check()){
//            set_messenger_profile(auth('company')->user());
//        }
//        elseif (session()->get('character')){
//            $character = Character::find(session()->get('character'));
//            set_messenger_profile($character);
//        }

        set_messenger_profile(auth()->user());

        return $next($request);
    }
}
