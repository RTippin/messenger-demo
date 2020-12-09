<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(): Renderable
    {
        return view('home');
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
}
