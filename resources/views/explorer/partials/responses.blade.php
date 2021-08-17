<div class="row">
    <div class="col-12 col-xl-8 offset-xl-2 mt-3">
        <div class="card">
            <div class="card-header h3 text-info">
                <i class="fas fa-database"></i> <pre class="d-inline">Route Details: </pre>
                @if(! $standalone)
                    <span class="float-right"><a href="{{route('api-explorer.routes.show', $name)}}" target="_blank" title="Open in new window" class="ml-2 btn btn-sm btn-warning">Popout <i class="fas fa-external-link-alt"></i></a></span>
                @endif
            </div>
            <div class="card-body bg-light">
                <div class="col-12">
                    <span class="h3">Route name:</span> <span class="h4 ml-2"><span class="badge badge-light">{{ $name }}</span></span>
                    <hr>
                    <span class="h3">URI:</span> <span class="h4 ml-2"><span class="badge badge-light">{{ $uri }}</span></span>
                    <hr>
                    <span class="h3">Methods:</span> <span class="h4 ml-2"><span class="badge badge-light">{{ $methods }}</span></span>
                    <hr>
                    <span class="h3">Example Query:</span><br>
                    <div class="mt-2 h5"><span class="bg-dark">{{ $query }}</span></div>
                </div>
            </div>
        </div>
    </div>
    @foreach($responses as $status => $response)
        <div class="col-12 col-xl-8 offset-xl-2 mt-3">
            <div class="card">
                <div class="card-header h3 text-info"><i class="fas fa-network-wired"></i> <pre class="d-inline">{{ $verb }} {{$status}}: </pre></div>
                <div class="card-body bg-light py-0 my-0">
                    @if(in_array($verb, ['GET', 'DELETE']))
                        @include('explorer.partials.get', ['data' => $response['response']])
                    @endif
                    @if(in_array($verb, ['POST', 'PUT', 'PATCH']))
                        @include('explorer.partials.post', ['data' => $response, 'status' => $status])
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
