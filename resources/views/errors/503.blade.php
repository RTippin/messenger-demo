@extends('layouts.app')
@section('title'){{ config('app.name') }} - Site Down @endsection
@section('content')
<div class="container mt-5">
    <div class="jumbotron bg-dark text-light pb-1">
        <div class="float-right d-none d-sm-block">
            <img id="FSlog" height="95" src="{{asset('vendor/messenger/images/messenger.png')}}">
        </div>
        <h3 class="display-4"><i class="fas fa-exclamation-triangle"></i> Site Down</h3>
        <p class="lead mt-5">Messenger Demo is currently down for maintenance.</p>
        <div class="mt-3 col-12 text-center flip-loader-container">
            <div class="flip-loader"><div></div><div></div><div></div></div>
        </div>
    </div>
</div>
@endsection
