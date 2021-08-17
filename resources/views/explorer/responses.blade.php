@extends('messenger::app')

@section('title')
    {{config('messenger-ui.site_name')}} - {{ $name }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="mt-3">
            @include('explorer.partials.responses')
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.1.0/styles/monokai.min.css">
@endpush
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.1.0/highlight.min.js"></script>
@endpush
@push('special-js')
    <script>
        document.querySelectorAll('code:not(.hljs)').forEach((block) => {
            hljs.highlightElement(block)
        });
    </script>
@endpush
