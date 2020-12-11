@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="col-12 bg-gradient-dark text-light p-3">
        <div class="d-flex w-100 justify-content-md-center">
            <img id="RTlog" src="{{asset('vendor/messenger/images/messenger.png')}}" height="75" width="75" class="d-none d-sm-block rounded">
            <div class="align-self-center ml-3">
                <span class="display-4"><i class="fas fa-sign-in-alt"></i> Messenger Register</span>
            </div>
        </div>
    </div>
</div>
@include('auth.partials.register')
@include('demo')
@endsection
