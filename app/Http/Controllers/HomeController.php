<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function getDemoAccounts(): JsonResponse
    {
        $users = User::demo()
            ->get()
            ->shuffle()
            ->filter(fn (User $user) => $user->getProviderOnlineStatus() === 0)
            ->take(5);

        return new JsonResponse([
            'html' => view('auth.demoAcc')->with('users', $users)->render()
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
