@extends('messenger::app')

@section('title')
    {{config('messenger-ui.site_name')}} - {{ $title }}
@endsection

@section('content')
    <div class="container-fluid">
        @include('docs.partials.menu')
        <div class="row mt-3">
            <div class="col-12 col-xl-10 offset-xl-1">
                <div class="card">
                    <div id="readmeContent" class="card-body">
                        {{ $markdown }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('layouts.highlighting')
