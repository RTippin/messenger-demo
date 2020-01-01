@extends('layouts.error')
@section('title'){{ config('app.name') }} - Site Down @endsection
@section('content')
<div class="container">
    <div class="jumbotron bg-gradient-dark text-light pb-1">
        <div class="float-right d-none d-sm-block">
            <img id="RTlog" height="95" src="{{asset('images/tipz.png')}}">
        </div>
        <h3 class="display-4"><i class="fas fa-exclamation-triangle"></i> Site Down</h3>
        <p class="lead mt-5">Messenger is currently down for maintenance. This page will automatically reload once our maintenance has concluded. Thank you for your patience</p>
        @if($exception->getMessage())
            <h4><i class="fas fa-info-circle"></i> Notice: {{$exception->getMessage()}}</h4>
        @endif
        <div class="mt-3 col-12 text-center flip-loader-container">
            <div class="flip-loader"><div></div><div></div><div></div></div>
        </div>
    </div>
</div>
@endsection
@push('special-js')
    <script>
        setInterval(function(){
            TippinManager.heartbeat().gather(function(){
                window.location.reload()
            }, null);
        }, 20000);
    </script>
@endpush
