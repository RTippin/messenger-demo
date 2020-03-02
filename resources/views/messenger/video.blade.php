@extends('layouts.popup')
@section('title'){{$thread->name}} - Video Call @endsection
@section('content')
    <div id="videos" class="container-fluid px-0 h-100">
        <div class="mt-2" id="other_videos_ctrn">
            <div id="empty_room" class="mt-2 col-12 px-0">
                <div class="col-12 col-sm-4 bg-gradient-dark shadow-lg rounded mx-auto pt-3">
                    <div class="col-12 text-center flip-loader-container">
                        <h5 class="text-light text-center">Waiting for others to join</h5>
                        <div class="flip-loader"><div></div><div></div><div></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mine_video_call NS" id="my_video_ctrn"></div>
    </div>
    <div id="hang_up" class="fixed-bottom">
        <nav id="RT_navbar" class="navbar fixed-bottom navbar-expand navbar-dark bg-dark">
            <div class="navbar-collapse collapse justify-content-start">
                <ul id="video_main_nav" class="navbar-nav">
                    @if($call_admin && $thread->ttype === 2)
                        <li id="end_wb_btn" class="nav-item mr-2">
                            <button data-toggle="tooltip" title="End Call" data-placement="top" id="end_call_btn" onclick="WebRTCManager.hangUp(true)" class="btn btn-warning pt-1 pb-0 px-2"><i class="fas fa-window-close fa-2x"></i></button>
                        </li>
                    @endif
                    <li id="end_wb_btn" class="nav-item mr-2">
                        <button data-toggle="tooltip" title="Leave Call" data-placement="top" id="hang_up_btn" onclick="WebRTCManager.hangUp(false)" class="btn btn-danger pt-1 pb-0 px-2"><i class="fas fa-phone-slash fa-2x"></i></button>
                    </li>
                </ul>
            </div>
            <ul id="video_main_nav2" class="navbar-nav justify-content-end">
                <li class="nav-item mr-2 rtc_nav_opt rtc_nav_video NS">
                    <button onclick="WebRTCManager.changeState({action : 'disable_vid'})" data-toggle="tooltip" title="Disable video" data-placement="top" class="btn btn-outline-success pt-1 pb-0 px-2 rtc_video_on rtc_nav_opt NS"><i class="fas fa-video fa-2x"></i></button>
                    <button onclick="WebRTCManager.changeState({action : 'enable_vid'})" data-toggle="tooltip" title="Enable video" data-placement="top" class="btn btn-outline-danger pt-1 pb-0 px-2 rtc_video_off rtc_nav_opt NS"><i class="fas fa-video-slash fa-2x"></i></button>
                </li>
                <li class="nav-item mr-2 rtc_nav_opt rtc_nav_audio NS">
                    <button onclick="WebRTCManager.changeState({action : 'mute_mic'})" data-tooltip="tooltip" title="Mute mic" data-placement="top" class="btn btn-outline-success pt-1 pb-0 px-3 rtc_audio_on rtc_nav_opt NS"><i class="fas fa-microphone fa-2x"></i></button>
                    <button onclick="WebRTCManager.changeState({action : 'unmute_mic'})" data-tooltip="tooltip" title="Unmute mic" data-placement="top" class="btn btn-outline-danger pt-1 pb-0 px-2 rtc_audio_off rtc_nav_opt NS"><i class="fas fa-microphone-slash fa-2x"></i></button>
                </li>
                @if(!agent()->isMobile())
                    <li class="nav-item mr-2 rtc_nav_opt rtc_nav_screen NS">
                        <button onclick="WebRTCManager.changeState({action : 'share_screen'})" data-tooltip="tooltip" title="Share screen" data-placement="top" class="btn btn-outline-info pt-1 pb-0 px-2 rtc_screen_off rtc_nav_opt NS"><i class="fas fa-desktop fa-2x"></i></button>
                        <button onclick="WebRTCManager.changeState({action : 'stop_screen'})" data-tooltip="tooltip" title="Stop screen share" data-placement="top" class="btn btn-outline-success pt-1 pb-0 px-2 rtc_screen_on rtc_nav_opt glowing_warning_btn NS"><i class="fas fa-desktop fa-2x"></i></button>
                    </li>
                @endif
                <li class="nav-item">
                    <button onclick="WebRTCManager.callSettings()" data-toggle="tooltip" title="Call Settings" data-placement="top" class="btn text-secondary btn-light pt-1 pb-0 px-2" type="button"><i class="fas fa-cog fa-2x"></i></button>
                </li>
            </ul>
        </nav>
    </div>
@stop
@push('TippinManager-load')
    WebRTCManager : {src : '{{mix("js/managers/WebRTCManager.js")}}'},
@endpush
@push('TippinManager-call')
    call : {
        call_id : '{{$call->id}}',
        call_type : 1,
        call_mode : {{$call->mode}},
        call_owner : '{{$call->owner_id}}',
        created_at : '{{$call->created_at}}',
        thread_id : '{{$thread->id}}',
        thread_type : {{$thread->ttype}},
        thread_name : '{{$thread->name}}',
        call_admin : {{$call_admin ? 'true' : 'false'}}
    },
@endpush
@push('css')
    <link href="{{ mix("css/calls.css") }}" rel="stylesheet">
    @include('layouts.bgGradient')
@endpush
