<?php
namespace App\Http\Controllers;

use App\Models\Messages\Messenger;
use App\User;
use Illuminate\Database\Eloquent\Builder;

class SearchController extends Controller
{
    public function search($query)
    {
        $classes = [];
        foreach (config('messenger.models') as $class){
            array_push($classes, $class);
        }
        $profiles = collect([]);
        $query_string =  str_replace( ['%', '<', '>'],  '', $query);

        $names = collect(preg_split('/[\ \n\,]+/', $query_string))
            ->filter(function ($value){
                return strlen($value) >= 3;
            })->uniqueStrict()->take(4);
        if(!empty($query_string) && $query_string !== null && strlen($query_string) >= 3) {
            $search = Messenger::whereHasMorph(
                'owner',
                $classes,
                function (Builder $query, $type) use($query_string, $names) {
                    if ($type === User::class) {
                        $query->where(function(Builder $query) use($names){
                            $names->each(function ($name) use($query){
                                $query->orWhere('first', 'LIKE', '%' . $name . '%');
                                $query->orWhere('last', 'LIKE', '%' . $name . '%');
                            });
                        })->orWhere('email', $query_string);
                    }
//                    if ($type === Character::class) {
//                        $query->where('character_name', 'LIKE', "%{$query_string}%");
//                    }
                }
            )->limit(25)->get();
            if($search){
                foreach($search as $item){
                    if(messenger_profile()->is($item->owner)) continue;
                    $profiles->push([
                        'id' => $item->owner->id,
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
}

