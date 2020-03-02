@extends('layouts.app')
@section('title')
    {{ config('app.name', 'Tipz Messenger') }} - Join Group
@endsection
@push('css')
    @include('layouts.bgGradient')
@endpush
@section('content')
    <div class="container">
        <div class="jumbotron bg-gradient-dark text-light">
            <div class="float-right d-none d-sm-block pl-2">
                <img class="pl-2" id="FSlog" height="95" src="{{asset('images/navFS.png')}}">
            </div>
            <h1 class="display-4"><i class="fas fa-users"></i> {{$invite->thread->name}}</h1>
            {!! Auth::check() && !$can_join ? '' : '<h3 class="mt-3"><i class="far fa-dot-circle"></i> Join this group on Messenger'.($can_join ? '?' : '!').'</h3>'!!}
            <p class="h4 mt-4 text-warning">
                <i class="far fa-dot-circle"></i>
                @if(Auth::check())
                    {{$can_join ? ' Click join below to be added to this group. You will then be redirected into the group conversation' : ' You are already in this group'}}
                @else
                    Before you may join the group, you must log in or sign up below
                @endif
            </p>
        </div>
    </div>
    @if(Auth::check())
        @if($can_join)
            <div id="join_ctnr" class="col-12 text-center mt-5">
                <button onclick="window.location.replace('/')" type="button" data-toggle="tooltip" data-placement="left" title="Cancel" class="mx-3 mb-4 shadow-lg btn btn-circle btn-circle-xl btn-danger">No <i class="fas fa-times"></i></button>
                <button onclick="requestJoin()" type="button" data-toggle="tooltip" data-placement="right" title="Join Group!" class="mx-3 mb-4 shadow-lg btn btn-circle btn-circle-xl btn-success">Join <i class="fas fa-users"></i></button>
            </div>
        @else
            <div class="col-12 text-center mt-5">
                <button onclick="window.location.href='/messenger/{{$invite->thread_id}}'" type="button" data-toggle="tooltip"
                        data-placement="bottom" title="View Group" class="shadow-lg btn btn-circle btn-circle-xl btn-success">Enter <i class="fas fa-users"></i></button>
            </div>
        @endif
    @else
        <div id="login_area">
            @include('auth.partials.login')
            <div class="col-12 text-center mt-5">
                <button onclick="showReg()" data-toggle="tooltip" data-placement="top" title="New? Sign Up!" type="button" class="shadow-lg btn btn-circle btn-circle-xl btn-primary">New? <i class="fas fa-user-check"></i></button>
            </div>
        </div>
        <div id="register_area" class="NS">
            <div class="col-12 text-center mb-5">
                <button onclick="showLogin()" data-toggle="tooltip" data-placement="bottom" title="Have account? Log In!" type="button" class="shadow-lg btn btn-circle btn-circle-xl btn-primary">Log In <i class="fas fa-sign-in-alt"></i></button>
            </div>
            @include('auth.partials.register')
        </div>
    @endif
@endsection
@push('special-js')
    <script>
    @if(Auth::check())
        @if($can_join)
            let requestJoin = function () {
                $("#join_ctnr").html(TippinManager.alert().loader());
                TippinManager.xhr().payload({
                    route : '/demo-api/messenger/join/{{request()->slug}}',
                    data : {
                        action : 'join',
                        slug : '{{$invite->slug}}'
                    },
                    success : function () {
                        TippinManager.alert().Alert({
                            toast : true,
                            theme : 'success',
                            title : 'Joined! Loading group now...'
                        });
                        window.location.replace('/messenger/{{$invite->thread_id}}')
                    },
                    fail : function () {
                        setTimeout(function () {
                            window.location.reload()
                        }, 3000)
                    },
                    bypass : true
                })
            };
        @endif
    @else
        let showReg = function(){
                $("#login_area").slideUp();
                $("#register_area").slideDown();
                grecaptcha.reset()
            },
            showLogin = function(){
                $("#login_area").slideDown();
                $("#register_area").slideUp();
            };
    @endif
    PageListeners.listen().animateLogo({elm : "#FSlog"});
    </script>
@endpush
@if(Auth::guest())
    @push('js') <script src='https://www.google.com/recaptcha/api.js'></script> @endpush
@endif
