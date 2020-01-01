<a onclick="void(0); return false;" href="#" class="list-group-item list-group-item-action p-2 {{$data['read_at'] === NULL ? 'bg-warning' : ''}}">
    <div class="media">
        <div class="media-left media-top">
            <img class="rounded media-object" height="50" width="50" src="{{$data['image']}}">
        </div>
        <div class="media-body">
            <h6 class="ml-2 mb-1">{{$data['name']}} accepted your friend request. You are now connected</h6>
        </div>
    </div>
    <div class="float-right"><small>{{$data['created_at']->format('n/j/y \\- g:i a')}} <i class="far fa-calendar-alt"></i></small></div>
</a>