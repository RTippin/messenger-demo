<?php
namespace App\Http\Controllers;

use App\Models\Messages\Messenger;
use Illuminate\Http\Request;
use View;

class SearchController extends Controller
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index()
    {
        $classes = [];
        foreach (config('messenger.models') as $class){
            array_push($classes, $class);
        }
        $profiles = [];
        $query_string =  str_replace( ['%', '<', '>'],  '', $this->request->input('query'));
        if(!empty($query_string) && $query_string != null && strlen($query_string) >= 3) {
            $profiles = Messenger::whereHasMorph(
                'owner',
                $classes,
                function ($query) use($query_string) {
                    $query->where('firstName', 'LIKE', "%{$query_string}%")
                        ->orWhere('lastName', 'LIKE', "%{$query_string}%")
                        ->orWhere('email', $query_string);
                }
            )->get();
        }
        return View::make('search', compact('profiles'));
    }
}

