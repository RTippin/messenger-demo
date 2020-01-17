<div class="{{agent()->isMobile() ? '' : 'px-3'}} mt-2">
    @if($networks->count())
        <div class="table-responsive-sm">
            <table id="contact_list_table" class="table table-sm table-hover table-striped">
                <thead class="bg-gradient-dark text-light">
                <tr>
                    <th>Name</th>
                    <th class="{{agent()->isMobile() ?? 'NS'}}"><span class="float-right">Added On</span></th>
                </tr>
                </thead>
                <tbody>
                @foreach($networks as $network)
                    <tr onclick="ThreadManager.load().createPrivate({slug : '{{$network->party->slug()}}', type : '{{get_messenger_alias($network->party)}}'})" class="pointer_area">
                        <td>
                            <div class="table_links">
                                <div class="nowrap">
                                    <img class="rounded-circle group-image avatar-is-{{$network->party->onlineStatus()}}" src="{{asset($network->party->avatar())}}"/>
                                    <span class="h5"><span class="badge badge-light">{{$network->party->name}}</span></span>
                                </div>
                            </div>
                        </td>
                        <td class="{{agent()->isMobile() ?? 'NS'}}">
                            <div class="float-right nowrap">
                                <span class="mt-2 shadow-sm badge badge-secondary">{{$network->party->created_at->format('Y-m-d')}} <i class="far fa-calendar-alt"></i></span>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <h4 class="text-center mt-4"><span class="badge badge-pill badge-secondary"><i class="fas fa-user-friends"></i> No contacts to show</span></h4>
    @endif
</div>
