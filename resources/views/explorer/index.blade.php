@extends('messenger::app')

@section('title')
    {{config('messenger-ui.site_name')}} - API Explorer
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-xl-8 offset-xl-2">
            <div class="card">
                <div class="card-header h3 text-info text-center"><i class="fas fa-hand-point-down"></i> Please select a route below to view responses</div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12 col-xl-8 offset-xl-2">
            <div class="card">
                <div class="card-header h3 text-info"><i class="fas fa-link"></i> <pre class="d-inline">API Routes</pre></div>
                <div id="route_list_container" class="card-body">
                    <div class="col-12 my-2 text-center"><div class="spinner-grow text-primary" role="status"></div></div>
                </div>
            </div>
        </div>
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
        window.routeListContainer = $("#route_list_container");

        Messenger.xhr().request({
            route : '{{ route('api-explorer.routes')  }}',
            success : function(data) {
                routeListContainer.html(data.html);
                $("#route_list").DataTable({
                    "order": []
                })
            },
        });

        window.loadRoute = function(route){
            Messenger.alert().Modal({
                icon : 'link',
                backdrop_ctrl : false,
                theme : 'dark',
                title : 'Loading Responses...',
                pre_loader : true,
                unlock_buttons : false,
                h4 : false,
                size : 'fullscreen',
                onReady : function(){
                    Messenger.xhr().request({
                        route : '/api-explorer/routes/'+route,
                        success : function(data){
                            updateCurrentRoute(data.html, route)
                        },
                    });
                }
            });
        };

        window.updateCurrentRoute = function(html, route){
            Messenger.alert().fillModal({
                body : html,
                title : route
            });
            document.querySelectorAll('code:not(.hljs)').forEach((block) => {
                hljs.highlightElement(block)
            });
        };
    </script>
@endpush
