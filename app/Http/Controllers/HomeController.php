<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * Show the application config.
     *
     * @return Renderable
     */
    public function config(): Renderable
    {
        return view('config');
    }

    /**
     * @return JsonResponse
     */
    public function getDemoAccounts(): JsonResponse
    {
        $users = User::demo()->get()->shuffle()->filter(function (User $user){
            return $user->onlineStatus() === 0;
        });

        return new JsonResponse([
            'html' => view('auth.demoAcc')->with('users', $users->take(5))->render()
        ]);
    }
    /**
     * @return JsonResponse
     */
    public function csrfHeartbeat(): JsonResponse
    {
        return new JsonResponse([
            'auth' => true,
        ]);
    }
}
