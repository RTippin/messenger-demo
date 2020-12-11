@extends('messenger::app')
@section('title')
    Join Group
@endsection
@push('css')
    <style>
        body {
            background: #3d9a9b;
        }
    </style>
@endpush
@section('content')
    <div class="container">
        <div class="jumbotron bg-gradient-dark text-white">
            <div id="inv_loading" class="text-center">
                <h2>Join with Invite <div class="spinner-grow text-primary" role="status"></div></h2>
            </div>
            <div id="inv_loaded"></div>
        </div>
    </div>
    <div id="inv_actions_ctnr" class="col-12 text-center mt-5 NS"></div>
    @guest
        <div id="auth_flow" class="NS">
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
        </div>
    @endguest
@endsection
@push('Messenger-modules')
    InviteJoin : {
    src : 'InviteJoin.js',
    code : '{{$code}}'
    },
@endpush
@guest
    @push('special-js')
        <script>
            let showReg = function(){
                    $("#login_area").slideUp();
                    $("#register_area").slideDown();
                },
                showLogin = function(){
                    $("#login_area").slideDown();
                    $("#register_area").slideUp();
                };
        </script>
    @endpush
@endguest
