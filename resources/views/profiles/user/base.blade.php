@extends('layouts.app')
@section('seo')
    @include('seo.user')
@endsection
@section('content')
<div class="container-fluid">
    <div class="pb-2 mb-2 border-bottom">
        <div class="media">
            <div class="media-left media-top">
                <div class="expImg">
                    <img src="{{$userViewing->avatar()}}" height="75" width="75" class="rounded media-object">
                    <div class="img_overlay"><div class="img_text">View</div></div>
                </div>
            </div>
            <div class="media-body">
                <div class="col-12 noPad">
                    <span class="h3 page-header">{{$userViewing->name}}</span><br/>
                    <span class="h6 page-header"><strong>Profile</strong> <i class="far fa-user-circle"></i></span>
                    @if(Auth::check() && $user->id !== $userViewing->id)
                        <span class="float-right">
                            <span id="network_for_{{$userViewing->id}}">
                            @switch($con)
                                @case(0)
                                <button id="add_network_{{$userViewing->id}}" data-toggle="tooltip" title="Add friend" data-placement="top" class="btn btn-success pt-1 pb-0 px-2" onclick="NetworksManager.action({action : 'add', slug : '{{$userViewing->slug()}}', type : 'user', owner_id : '{{$userViewing->id}}'});"><i class="fas fa-user-plus fa-2x"></i></button>
                                @break
                                @case(1)
                                <button id="remove_network_{{$userViewing->id}}" data-toggle="tooltip" title="Remove friend" data-placement="top" class="btn btn-danger pt-1 pb-0 px-2" onclick="NetworksManager.action({action : 'remove', slug : '{{$userViewing->slug()}}', type : 'user', owner_id : '{{$userViewing->id}}'});"><i class="fas fa-user-times fa-2x"></i></button>
                                @break
                                @case(2)
                                <button id="cancel_network_{{$userViewing->id}}" data-toggle="tooltip" title="Cancel friend request" data-placement="top" class="btn btn-danger pt-1 pb-0 px-2" onclick="NetworksManager.action({action : 'cancel', slug : '{{$userViewing->slug()}}', type : 'user', owner_id : '{{$userViewing->id}}'});"><i class="fas fa-ban fa-2x"></i></button>
                                @break
                                @case(3)
                                <button id="accept_network_{{$userViewing->id}}" data-toggle="tooltip" title="Accept friend request" data-placement="top" class="btn btn-success pt-1 pb-0 px-2" onclick="NetworksManager.action({action : 'accept', slug : '{{$userViewing->slug()}}', type : 'user', owner_id : '{{$userViewing->id}}'});"><i class="far fa-check-circle fa-2x"></i></button>
                                <button id="deny_network_{{$userViewing->id}}" data-toggle="tooltip" title="Deny friend request" data-placement="top" class="btn btn-danger pt-1 pb-0 px-2" onclick="NetworksManager.action({action : 'deny', slug : '{{$userViewing->slug()}}', type : 'user', owner_id : '{{$userViewing->id}}'});"><i class="fas fa-ban fa-2x"></i></button>
                                @break
                            @endswitch
                            </span>
                            <button onclick="window.location.href='{{$userViewing->slug(true)}}/message';" data-toggle="tooltip" title="Message" data-placement="top" class="btn btn-primary pt-1 pb-0 px-2" type="button"><i class="fas fa-comments fa-2x"></i></button>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-12">
            @include('profiles.user.general')
        </div>
    </div>
</div>
@endsection
@push('special-js')
    <script>
        var user_network_table = $("#user_networks_table");
        $(".expImg").on("click", function(){
            TippinManager.alert().Modal({
                theme : 'dark',
                title : '{{ $userViewing ->name }}\'s Photo',
                body : '<div class="text-center"><img src="{{$userViewing->avatar(true)}}" class="img-fluid rounded" /></div>'
            });
        });
        if(user_network_table.length) user_network_table.DataTable();
        PageListeners.listen().animateLogo({elm : "#RTlog"});
        if (!$('.portfolio_sec').length){
            $("#empty_portfolio").show();
        }
    </script>
@endpush
