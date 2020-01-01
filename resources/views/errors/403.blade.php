@extends('layouts.error')
@section('title'){{ config('app.name') }} - Not Authorized @endsection
@section('content')
<div class="container">
    <div class="jumbotron bg-gradient-dark text-light">
        <div class="float-right d-none d-sm-block">
            <img id="RTlog" height="95" src="{{asset('images/tipz.png')}}">
        </div>
        <h3 class="display-4"><i class="fas fa-exclamation-triangle"></i> Not authorized</h3>
        <p class="lead">You are not authorized to view the page you requested</p>
    </div>
    <a href="/" class="btn btn-block btn-md btn-danger"><strong>Return Home <i class="fas fa-home"></i></strong></a>
</div>
@endsection



