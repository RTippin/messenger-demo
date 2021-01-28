@extends('layouts.app')
@section('title'){{ config('app.name') }} - Error @endsection
@section('content')
<div class="container mt-5">
    <div class="jumbotron bg-dark text-light pb-1">
        <div class="float-right d-none d-sm-block">
            <img id="FSlog" height="95" src="{{asset('vendor/messenger/images/messenger.png')}}">
        </div>
        <h3 class="display-4"><i class="fas fa-exclamation-triangle"></i> Error</h3>
        <p class="lead mt-5">Your request has encountered an error.</p>
    </div>
    <a href="/" class="btn btn-block btn-md btn-danger"><strong>Return Home <i class="fas fa-home"></i></strong></a>
</div>
@endsection
