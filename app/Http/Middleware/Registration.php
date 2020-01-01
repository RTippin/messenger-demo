<?php

namespace App\Http\Middleware;

use Closure;

class Registration
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
        if(!config('app.registration')){
            if ($request->ajax()){
                return response()->json(['errors' => ['forms' => 'Registration is currently disabled.']], 400);
            }
            return response()->view('errors.custom', ['err' => 'noReg']);
        }
        return $next($request);
    }
}
