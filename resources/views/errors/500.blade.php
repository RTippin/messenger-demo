@extends('layouts.error')
@section('title'){{ config('app.name') }} - Error @endsection
@section('content')
<div class="container">
    <div class="jumbotron bg-gradient-dark text-light">
        <div class="float-right d-none d-sm-block">
            <img id="RTlog" height="95" src="{{asset('images/tipz.png')}}">
        </div>
        <h3 class="display-4"><i class="fas fa-exclamation-triangle"></i> Error</h3>
        <p class="lead">Your request has encountered an error. We have been made aware of this issue. If the problem persist, please contact us below </p>
        <div class="col-12 mt-3 text-center">
            <a class="btn btn-sm btn-primary" href="{{route('contact_us')}}"><i class="far fa-envelope"></i> Contact Us</a>
        </div>
    </div>
    <a href="/" class="btn btn-block btn-md btn-danger"><strong>Return Home <i class="fas fa-home"></i></strong></a>
</div>
@endsection
