@extends('layouts.app')
@section('seo')
    @include('seo.login')
@endsection
@push('css')
    @include('layouts.bgGradient')
@endpush
@section('content')
<div class="container mb-3">
    <div class="col-12 bg-gradient-dark text-light p-3">
        <div class="d-flex w-100 justify-content-md-center">
            <img id="RTlog" src="{{asset('images/tipz.png')}}" height="75" width="75" class="d-none d-sm-block rounded">
            <div class="align-self-center ml-3">
                <span class="display-4"><i class="fas fa-sign-in-alt"></i> Messenger Log In</span>
            </div>
        </div>
    </div>
</div>
@include('auth.partials.login')
@endsection
@push('special-js')
    <script>
        PageListeners.listen().animateLogo({elm : '#RTlog'});
    </script>
@endpush
