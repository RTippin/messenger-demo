<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class AuthViaRemember
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
        if(Auth::viaRemember()){
            if ($request->expectsJson()){
                return response()->json(['error' => 02, 'fill_teapot' => true, 'errors' => ['forms' => 'Session regenerating']], 418);
            }
            return redirect(request()->url());
        }
        return $next($request);
    }
}