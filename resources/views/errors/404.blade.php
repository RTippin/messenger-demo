@extends('layouts.error')
@section('title'){{ config('app.name') }} - Not Found @endsection
@section('content')
<div class="container">
    <div class="jumbotron bg-gradient-dark text-light">
        <div class="float-right d-none d-sm-block">
            <img id="RTlog" height="95" src="{{asset('images/tipz.png')}}">
        </div>
        <h3 class="display-4"><i class="fas fa-exclamation-triangle"></i> Not found</h3>
        <p class="lead">We could not locate the page you requested, it may have been lost forever</p>
        <code><h3>404</h3></code>
    </div>
    <a href="/" class="btn btn-block btn-md btn-danger"><strong>Return Home <i class="fas fa-home"></i></strong></a>
</div>
@endsection
