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
<div class="container mt-4">
    <div class="col-12 col-lg-8 offset-lg-2 px-0">
        <div class="card bg-gradient-light rounded shadow">
            <div class="card-header bg-gradient-dark text-light py-1">
                <span class="h4"><strong><i class="fas fa-users"></i> Available Accounts</strong></span>
            </div>
            <div id="available_acc_elm" class="card-body p-2">
                <div class="col-12 my-2 text-center"><div class="spinner-border text-primary" role="status"></div></div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('special-js')
    <script>
        PageListeners.listen().animateLogo({elm : '#RTlog'});
        TippinManager.xhr().request({
            route : '/auth/accounts',
            success : function(data){
                $("#available_acc_elm").html(data.html);
            },
            fail : null
        });
    </script>
@endpush
