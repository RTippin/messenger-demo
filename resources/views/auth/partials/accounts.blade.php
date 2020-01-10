@if(count($users))
    <div class="table-responsive-sm">
        <table id="search_users_table" class="table table-sm table-hover table-striped">
            <tbody>
            @foreach($users as $key => $item)
                <tr>
                    <td>
                        <div class="table_links">
                            <div class="nowrap">
                                <a href="{{$item->slug(true)}}">
                                    <img class="rounded group-image" src="{{asset($item->avatar())}}"/>
                                    <span class="h5"><span class="badge badge-light">{{$item->name}}</span></span>
                                </a>
                            </div>
                        </div>
                    </td>
                    <td>
                        <button class="float-right btn btn-sm btn-primary" onclick="GuestManager.special('{{$item->email}}');"><i class="fas fa-sign-in-alt"></i> Use Account</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="col-12 text-center my-3">
        <h4>No available accounts. Please refresh or signup</h4>
    </div>
@endif

