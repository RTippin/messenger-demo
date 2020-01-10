<div class="row">
    <div class="col-12 show_datatable NS">
        <div class="table-responsive-sm">
            <table id="view_group_participants" class="table table-sm table-hover">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($participants as $participant)
                    <tr id="row_{{$participant->id}}">
                        <td>
                            <div class="participant_link table_links">
                                <div class="nowrap">
                                    <a target="_blank" href="{{$participant->owner->slug(true)}}">
                                        <img class="rounded-circle group-image avatar-is-{{$participant->owner->onlineStatus()}}" src="{{asset($participant->owner->avatar())}}"/>
                                        <span class="h5"><span class="badge badge-light">{{$participant->owner->name}}</span></span>
                                    </a>
                                    <span class="participant_admin_{{$participant->id}}">{!! $participant->admin ? '<span class="badge badge-warning">Admin</span>' : '' !!}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="dropdown float-right">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown"><i class="fas fa-cog"></i></button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" onclick="ThreadManager.load().createPrivate({slug : '{{$participant->owner->slug()}}', type : '{{get_messenger_alias($participant->owner)}}'}); return false;" target="_blank"
                                       href="{{$participant->owner->slug(true)}}/message" title="Message"><i class="far fa-comment-alt"></i> Message
                                    </a>
                                    @if($owner)
                                        <a class="dropdown-item" onclick="ThreadManager.group().removeParticipant('{{$participant->id}}'); return false;" href="#" title="Remove"><i class="fas fa-trash-alt"></i> Remove</a>
                                        @if($participant->admin)
                                            <a class="dropdown-item" onclick="ThreadManager.group().adminParticipant({type : 'participant_admin_revoke', id : '{{$participant->id}}'}); return false;"
                                               href="#" title="Revoke admin"><i class="fas fa-user-shield"></i> Revoke admin
                                            </a>
                                        @else
                                            <a class="dropdown-item" onclick="ThreadManager.group().adminParticipant({type : 'participant_admin_grant', id : '{{$participant->id}}'}); return false;"
                                               href="#" title="Make admin"><i class="fas fa-chess-queen"></i> Make admin
                                            </a>
                                        @endif
                                    @endif
                                    <span id="network_for_{{$participant->owner->id}}">
                                        @switch(messenger_profile()->networkStatus($participant->owner))
                                            @case(0)
                                            <a class="network_option dropdown-item" onclick="NetworksManager.action({dropdown : true, owner_id : '{{$participant->owner->id}}', action : 'add', slug : '{{$participant->owner->slug()}}', type : '{{get_messenger_alias($participant->owner)}}'}); return false;" href="#">
                                                <i class="fas fa-user-plus"></i> Add friend</a>
                                            @break
                                            @case(1)
                                            <a class="network_option dropdown-item" onclick="NetworksManager.action({dropdown : true, owner_id : '{{$participant->owner->id}}', action : 'remove', slug : '{{$participant->owner->slug()}}', type : '{{get_messenger_alias($participant->owner)}}'}); return false;" href="#">
                                                <i class="fas fa-user-times"></i> Remove friend</a>
                                            @break
                                            @case(2)
                                            <a class="network_option dropdown-item" onclick="NetworksManager.action({dropdown : true, owner_id : '{{$participant->owner->id}}', action : 'cancel', slug : '{{$participant->owner->slug()}}', type : '{{get_messenger_alias($participant->owner)}}'}); return false;" href="#">
                                                <i class="fas fa-ban"></i> Cancel friend request</a>
                                            @break
                                            @case(3)
                                            <a class="network_option dropdown-item" onclick="NetworksManager.action({dropdown : true, owner_id : '{{$participant->owner->id}}', action : 'accept', slug : '{{$participant->owner->slug()}}', type : '{{get_messenger_alias($participant->owner)}}'}); return false;" href="#">
                                                <i class="fas fa-check"></i> Accept friend request</a>
                                            <a class="network_option dropdown-item" onclick="NetworksManager.action({dropdown : true, owner_id : '{{$participant->owner->id}}', action : 'deny', slug : '{{$participant->owner->slug()}}', type : '{{get_messenger_alias($participant->owner)}}'}); return false;" href="#">
                                                <i class="fas fa-ban"></i> Deny friend request</a>
                                            @break
                                        @endswitch
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
