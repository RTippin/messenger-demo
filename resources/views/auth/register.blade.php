@extends('layouts.app')
@section('seo')
    @include('seo.register')
@endsection
@push('css')
    @include('layouts.bgGradient')
@endpush
@push('js') <script src='https://www.google.com/recaptcha/api.js'></script> @endpush
@section('content')
<div class="container">
    <div class="jumbotron bg-gradient-dark text-light">
    <div class="float-right d-none d-sm-block pl-2">
        <img class="pl-2" id="RTlog" height="95" src="{{asset('images/tipz.png')}}">
    </div>
        <h3 class="display-4"><i class="fas fa-user-plus"></i> Sign Up</h3>
        <p class="h4">Sign up to use our Laravel Messenger demo! All accounts and files uploaded will be wiped once each week.</p>
    </div>
</div>
@include('auth.partials.register')
@endsection
@push('special-js')
    <script>
        PageListeners.listen().animateLogo({elm : "#RTlog"});
    </script>
@endpush
