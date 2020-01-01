<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class CheckAccountActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth::check() && !auth()->user()->active){
            Auth::guard()->logout();
            $request->session()->invalidate();
            if ($request->expectsJson()){
                return response()->json(['errors' => ['forms' => 'Your account has been disabled']], 400);
            }
            return redirect('/');
        }
        return $next($request);
    }
}