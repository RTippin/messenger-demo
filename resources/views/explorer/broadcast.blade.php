@extends('messenger::app')

@section('title')
    {{config('messenger-ui.site_name')}} - {{ $class }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="mt-3">
            @include('explorer.partials.broadcast')
        </div>
    </div>
@endsection

@include('layouts.highlighting')
