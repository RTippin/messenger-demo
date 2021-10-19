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
                                Welcome to Tippin's Messenger demo! This laravel installation uses the
                                <strong><span class="text-info">rtippin/messenger</span></strong> package to utilize
                                a fully featured messenger system. You may sign up anytime or use a demo account
                                listed below to test out the features provided. Private and group threads
                                between multiple models, with real-time messaging, reactions, attachments, calling,
                                chat bots, and more!
                            </h4>
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
                <button onclick="window.location.href='{{ route('login') }}'" type="button" class="d-none d-sm-inline shadow-lg btn btn-circle btn-circle-xl btn-info">Log In <i class="fas fa-sign-in-alt"></i></button>
                <a class="d-block d-sm-none btn btn-lg btn-info" href="{{ route('login') }}">Log In <i class="fas fa-sign-in-alt"></i></a>
                <div class="mx-2 mt-2 d-block d-sm-inline h5"><span class="badge badge-dark"><i class="fas fa-angle-left"></i> <i class="fas fa-angle-right"></i></span></div>
                <button onclick="window.location.href='{{ route('register') }}'" type="button" class="d-none d-sm-inline shadow-lg btn btn-circle btn-circle-xl btn-info">New? <i class="fas fa-user-check"></i></button>
                <a class="d-block d-sm-none btn btn-lg btn-info" href="{{ route('register') }}">Sign Up <i class="fas fa-user-plus"></i></a>
            </div>
        </div>
    </div>
    @include('demo')
@endsection
