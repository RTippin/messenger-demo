@extends('layouts.app')
@section('title'){{ config('app.name') }} - Not Found @endsection
@section('content')
<div class="container mt-5">
    <div class="jumbotron bg-dark text-light pb-1">
        <div class="float-right d-none d-sm-block">
            <img id="FSlog" height="95" src="{{asset('vendor/messenger/images/messenger.png')}}">
        </div>
        <h3 class="display-4"><i class="fas fa-exclamation-triangle"></i> Not Found</h3>
        @if($exception->getMessage())
            <p class="lead mt-5"><i class="fas fa-info-circle"></i> {{$exception->getMessage()}}</p>
        @else
            <p class="lead mt-5">We could not locate the page you requested, it may have been lost forever</p>
        @endif
    </div>
    <a href="/" class="btn btn-block btn-md btn-danger"><strong>Return Home <i class="fas fa-home"></i></strong></a>
</div>
@endsection
