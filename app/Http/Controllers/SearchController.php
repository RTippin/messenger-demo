<?php
namespace App\Http\Controllers;

use App\Models\Messages\Messenger;
use App\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function search()
    {
        if($this->request->expectsJson()){
            $classes = [];
            foreach (config('messenger.models') as $class){
                array_push($classes, $class);
            }
            $profiles = collect([]);
            $query_string =  str_replace( ['%', '<', '>'],  '', $this->request->input('query'));
            if(!empty($query_string) && $query_string != null && strlen($query_string) >= 3) {
                $search = Messenger::whereHasMorph(
                    'owner',
                    $classes,
                    function ($query, $type) use($query_string) {
                        if ($type === User::class) {
                            $query->where('first', 'LIKE', "%{$query_string}%")
                                ->orWhere('last', 'LIKE', "%{$query_string}%")
                                ->orWhere('email', $query_string);
                        }
//                    if ($type === Charaacter::class) {
//                        $query->where('character_name', 'LIKE', "%{$query_string}%");
//                    }
                    }
                )->limit(25)->get();
                if($search){
                    foreach($search as $item){
                        if(messenger_profile()->is($item->owner)) continue;
                        $profiles->push([
                            'name' => $item->owner->name,
                            'avatar' => $item->owner->avatar(),
                            'online' => $item->owner->isOnline(),
                            'slug' => $item->owner->slug(),
                            'alias' => get_messenger_alias($item->owner),
                            'network' => messenger_profile()->networkStatus($item->owner)
                        ]);
                    }
                }
            }
            return response()->json([
                'results' => $profiles,
                'query' => $query_string
            ]);
        }
        return redirect()->route('messages');
    }
}

