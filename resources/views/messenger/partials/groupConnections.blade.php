<div class="row">
    <div class="col-12 show_datatable NS">
        <div class="table-responsive-sm">
            <table id="add_group_participants" class="table table-sm table-hover">
                <thead>
                <tr>
                    <th>Name</th>
                    <th><span class="float-right">Add</span></th>
                </tr>
                </thead>
                <tbody>
                @foreach($networks as $network)
                    <tr>
                        <td class="pointer_area" onclick="$(this).parent().children().find('.switch_input').click()">
                            <div class="nowrap">
                                <img class="rounded-circle group-image avatar-is-{{$network->party->onlineStatus()}}" src="{{asset($network->party->avatar())}}"/>
                                <span class="h5"><span class="badge badge-light">{{$network->party->name}}</span></span>
                            </div>
                        </td>
                        <td>
                            <div class="mt-1 float-right">
                            <span class="switch switch-sm mt-1">
                            <input onchange="ThreadManager.switchToggle()" class="switch switch_input" id="recipients[{{get_messenger_alias($network->party)}}_{{$network->party->id}}]" name="recipients[]" value="{{get_messenger_alias($network->party)}}_{{$network->party->id}}" type="checkbox" />
                            <label for="recipients[{{get_messenger_alias($network->party)}}_{{$network->party->id}}]" class=""></label>
                        </span>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
