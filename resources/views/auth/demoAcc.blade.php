@if(count($users))
    <div class="table-responsive-sm">
        <table id="search_users_table" class="table table-sm table-hover table-striped">
            <tbody>
            @foreach($users as $key => $item)
                <tr onclick="demoLogin('{{$item->email}}')" class="pointer_area">
                    <td>
                        <div class="table_links">
                            <div class="nowrap">
                                <img class="rounded group-image" src="{{asset($item->getAvatarRoute())}}"/>
                                <span class="h5"><span class="badge badge-light">{{$item->name()}}</span></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="mt-1 float-right h4">
                            <span class="shadow-sm badge badge-pill badge-primary"><i class="fas fa-sign-in-alt"></i> Use Account</span>
                        </div>
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
