@extends('messenger::app')

@section('title')
    {{config('messenger-ui.site_name')}} - API Explorer
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-xl-8 offset-xl-2">
            <div class="card">
                <div class="card-header h3 text-info"><i class="fas fa-link"></i> <pre class="d-inline">API Routes</pre></div>
                <div id="route_list_container" class="card-body">
                    <div class="col-12 my-2 text-center"><div class="spinner-grow text-primary" role="status"></div></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-xl-8 offset-xl-2">
            <div class="card">
                <div class="card-header h3 text-info"><i class="fas fa-database"></i> <pre class="d-inline">Route Details: </pre></div>
                <div id="route_info" class="card-body">
                    <h3 class="text-center"><i class="fas fa-hand-point-up"></i> Please select a route above to view details.</h3>
                </div>
            </div>
        </div>
    </div>
    <div id="responses_container" class="row"></div>
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
        Messenger.xhr().request({
            route : '{{ route('api-explorer.routes')  }}',
            success : function(data) {
                $("#route_list_container").html(data.html);
                $("#route_list").DataTable({
                    "order": []
                })
            },
        });
        window.infoContainer = $("#route_info");
        window.responsesContainer = $("#responses_container");

        window.loadRoute = function(route){
            infoContainer.html(Messenger.alert().loader(true));
            responsesContainer.html('');
            Messenger.xhr().request({
                route : '/api-explorer/routes/'+route,
                success : updateCurrentRoute,
            });
        };

        window.updateCurrentRoute = function(data){
            infoContainer.html(data.details);
            responsesContainer.html(data.data);
            document.querySelectorAll('code:not(.hljs)').forEach((block) => {
                hljs.highlightElement(block)
            })
        };
    </script>
@endpush
