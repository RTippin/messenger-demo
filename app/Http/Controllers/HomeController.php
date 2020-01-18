<?php
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use View;
use Cache;

class HomeController extends Controller
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function splash()
    {
        return view('splash');
    }

    public function availableAccounts()
    {
        $users = User::query()->oldest()->whereNotIn('email', ['admin@test.com', 'admin2@test.com', 'admin3@test.com'])->limit(15)->get()->shuffle()->reject(function ($user){
            return Cache::has(get_messenger_alias($user).'_online_'.$user->id) || Cache::has(get_messenger_alias($user).'_away_'.$user->id);
        });
        return response()->json([
            'html' => View::make('auth.partials.accounts')->with('users', $users->take(5))->render()
        ]);
    }
}
