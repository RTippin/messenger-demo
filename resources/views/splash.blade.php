@extends('layouts.app')
@section('content')
    <div class="pt-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-lg">
                        <h3 class="card-header"><i class="fas fa-comments"></i> Messenger Demo</h3>
                        <div class="card-body">
                            <h4>
                                Welcome to Tippin's Laravel Messenger demo! This is a simple  base laravel 8 installation using
                                <strong><span class="text-info">rtippin/messenger</span></strong> package to provide us with a full featured messenger system!
                                You may sign up anytime and test out our features currently provided, such as real time messaging/video
                                calling/screen sharing/group messaging, etc. We also provide a list of pre-populated users to choose
                                from so you can login right away. We will periodically reset this demo app.
                            </h4>
                            <hr>
                            <div class="col-12 mt-2 text-center h5">
                                <div class="h5 font-weight-bold mb-4">The source code for this demo project and it's installation docs can be found on my github @RTippin</div>
                                <a class="btn btn-lg btn-primary" target="_blank" href="https://github.com/RTippin/messenger"><i class="fab fa-github"></i> Package</a> |
                                <a class="btn btn-lg btn-primary" target="_blank" href="https://github.com/RTippin/messenger-demo"><i class="fab fa-github"></i> Demo</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="pt-5">
        <div class="container">
            <noscript>
                <div class="alert alert-danger shadow h4"><span class="float-right"><i class="fab fa-js-square fa-2x"></i></span> It appears your browser has javascript disabled. To continue using our website, you must first
                    <a class="alert-link" rel="nofollow" target="_blank" href="https://www.enable-javascript.com/"> enable javascript</a></div>
            </noscript>
            <div class="col-12 text-center">
                <button onclick="window.location.href='{{ route('login') }}'" type="button" class="d-none d-sm-inline shadow-lg btn btn-circle btn-circle-xl btn-dark">Log In <i class="fas fa-sign-in-alt"></i></button>
                <a class="d-block d-sm-none btn btn-lg btn-dark" href="{{ route('login') }}">Log In <i class="fas fa-sign-in-alt"></i></a>
                <div class="mx-2 mt-2 d-block d-sm-inline h5"><span class="badge badge-dark"><i class="fas fa-angle-left"></i> <i class="fas fa-angle-right"></i></span></div>
                <button onclick="window.location.href='{{ route('register') }}'" type="button" class="d-none d-sm-inline shadow-lg btn btn-circle btn-circle-xl btn-dark">New? <i class="fas fa-user-check"></i></button>
                <a class="d-block d-sm-none btn btn-lg btn-dark" href="{{ route('register') }}">Sign Up <i class="fas fa-user-plus"></i></a>
            </div>
        </div>
    </div>
    @include('demo')
@endsection
