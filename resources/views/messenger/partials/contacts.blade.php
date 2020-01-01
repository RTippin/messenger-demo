<div class="{{$user_agent->isMobile() ? '' : 'px-4'}} mt-2">
    @if($networks->count())
        <div class="table-responsive-sm">
            <table id="contact_list_table" class="table table-sm table-hover table-striped">
                <thead class="bg-gradient-dark text-light">
                <tr>
                    <th>Name</th>
                    <th><span class="float-right">Actions</span></th>
                </tr>
                </thead>
                <tbody>
                @foreach($networks as $network)
                    <tr>
                        <td>
                            <div class="table_links">
                                <div class="nowrap">
                                    <div class="nowrap">
                                        <a target="_blank" href="{{$network->party->slug(true)}}">
                                            <img class="rounded-circle group-image avatar-is-{{$network->party->onlineStatus()}}" src="{{asset($network->party->avatar())}}"/>
                                            <span class="h5"><span class="badge badge-light">{{$network->party->name}}</span></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="float-right nowrap">
                                <button id="remove_network_{{$network->party->slug()}}" data-toggle="tooltip" title="Remove friend" data-placement="left" class="btn btn-danger pt-1 pb-0 px-2"
                                        onclick="NetworksManager.action({action : 'remove', slug : '{{$network->party->slug()}}', type : '{{strtolower(class_basename($network->party))}}'})">
                                    <i class="fas fa-user-times fa-2x"></i></button>
                                <button data-toggle="tooltip" title="Message" data-placement="left" onclick="ThreadManager.load().createPrivate({slug : '{{$network->party->slug()}}', type : '{{strtolower(class_basename($network->party))}}'})" class="btn btn-primary pt-1 pb-0 px-2"><i class="fas fa-comments fa-2x"></i></button>
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