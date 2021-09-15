@extends('messenger::app')

@section('title')
    {{config('messenger-ui.site_name')}} - API Explorer
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-xl-6 mb-4">
            <div class="card">
                <div class="card-header h3 text-info text-center"><i class="fas fa-hand-point-down"></i> Select a route below to view responses</div>
            </div>
            <div class="card mt-2">
                <div class="card-header h3 text-info"><i class="fas fa-link"></i> <pre class="d-inline">API / Asset Routes</pre></div>
                <div id="route_list_container" class="card-body">
                    <div class="col-12 my-2 text-center"><div class="spinner-grow text-primary" role="status"></div></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card">
                <div class="card-header h3 text-info text-center"><i class="fas fa-hand-point-down"></i> Select a broadcast below to view data</div>
            </div>
            <div class="card mt-2">
                <div class="card-header h3 text-info"><i class="fas fa-bell"></i> <pre class="d-inline">Broadcast</pre></div>
                <div id="broadcast_list_container" class="card-body">
                    <div class="col-12 my-2 text-center"><div class="spinner-grow text-primary" role="status"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('layouts.highlighting')

@push('special-js')
    <script>
        window.routeListContainer = $("#route_list_container");
        window.broadcastListContainer = $("#broadcast_list_container");

        Messenger.xhr().request({
            route : '{{ route('api-explorer.routes')  }}',
            success : function(data) {
                routeListContainer.html(data.html);
                $("#route_list").DataTable({
                    "order": []
                })
            },
        });
        Messenger.xhr().request({
            route : '{{ route('api-explorer.broadcast')  }}',
            success : function(data) {
                broadcastListContainer.html(data.html);
                $("#broadcast_list").DataTable({
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

        window.loadBroadcast = function(broadcast){
            Messenger.alert().Modal({
                icon : 'bell',
                backdrop_ctrl : false,
                theme : 'dark',
                title : 'Loading Broadcast...',
                pre_loader : true,
                unlock_buttons : false,
                h4 : false,
                size : 'fullscreen',
                onReady : function(){
                    Messenger.xhr().request({
                        route : '/api-explorer/broadcasts/'+broadcast,
                        success : function(data){
                            updateCurrentBroadcast(data.html, broadcast)
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

        window.updateCurrentBroadcast = function(html, broadcast){
            Messenger.alert().fillModal({
                body : html,
                title : broadcast
            });
            document.querySelectorAll('code:not(.hljs)').forEach((block) => {
                hljs.highlightElement(block)
            });
        };
    </script>
@endpush
