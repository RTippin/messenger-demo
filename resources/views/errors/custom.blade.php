@extends('layouts.error')
@section('content')
<div class="container">
@if($err)
@if($err === 'badJoinLink')
    <div class="jumbotron bg-gradient-dark text-light">
        <div class="float-right d-none d-sm-block">
            <img id="RTlog" height="95" src="{{asset('images/tipz.png')}}">
        </div>
        <h3 class="display-4"><i class="fas fa-exclamation-triangle"></i> Invite Invalid</h3>
        <p class="lead">This invite may be expired, invalid, or you might not have permission to join</p>
    </div>
@elseif($err === 'noReg')
    <div class="jumbotron bg-gradient-dark text-light">
        <div class="float-right d-none d-sm-block">
            <img id="RTlog" height="95" src="{{asset('images/tipz.png')}}">
        </div>
        <h3 class="display-4"><i class="fas fa-exclamation-triangle"></i> Sign up disabled</h3>
        <p class="lead">Sign up's are currently disabled. Please check back with us soon</p>
    </div>
@elseif($err === 'noProfile')
    <div class="jumbotron bg-gradient-dark text-light">
        <div class="float-right d-none d-sm-block">
            <img id="RTlog" height="95" src="{{asset('images/tipz.png')}}">
        </div>
        <h3 class="display-4"><i class="fas fa-exclamation-triangle"></i> Profile not found</h3>
        <p class="lead">We could not locate the profile you are trying to access</p>
    </div>
@elseif($err === 'noDownload')
    <div class="jumbotron bg-gradient-dark text-light">
        <div class="float-right d-none d-sm-block">
            <img id="RTlog" height="95" src="{{asset('images/tipz.png')}}">
        </div>
        <h3 class="display-4"><i class="fas fa-exclamation-triangle"></i> Download error</h3>
        <p class="lead">You may not have permission to download this file, or it does not exist</p>
    </div>
@elseif($err === 'callError')
    <div class="jumbotron bg-gradient-dark text-light">
        <div class="float-right d-none d-sm-block">
            <img id="RTlog" height="95" src="{{asset('images/tipz.png')}}">
        </div>
        <h3 class="display-4"><i class="fas fa-exclamation-triangle"></i> Call Error</h3>
        <p class="lead">This call has ended or does not exist</p>
    </div>
    @push('special-js')
        <script>
            if(window.opener){
                window.opener.CallManager.popupNoCall();
                window.close()
            }
        </script>
    @endpush
@endif
@else
    <div class="jumbotron bg-gradient-dark text-light">
        <div class="float-right d-none d-sm-block">
            <img id="RTlog" height="95" src="{{asset('images/tipz.png')}}">
        </div>
        <h3 class="display-4"><i class="fas fa-exclamation-triangle"></i> Error</h3>
        <p class="lead">It appears something went wrong...sorry about that</p>
    </div>
@endif
    <a href="/" class="btn btn-block btn-md btn-danger"><strong>Return Home <i class="fas fa-home"></i></strong></a>
</div>
@endsection
