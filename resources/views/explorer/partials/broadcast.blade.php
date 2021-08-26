<div class="row">
    <div class="col-12 col-xl-8 offset-xl-2 mt-3">
        <div class="card">
            <div class="card-header h3 text-info">
                <i class="fas fa-database"></i> <pre class="d-inline">Broadcast Details: </pre>
                @if(! $standalone)
                    <span class="float-right"><a href="{{route('api-explorer.broadcast.show', $class)}}" target="_blank" title="Open in new window" class="ml-2 btn btn-sm btn-warning">Popout <i class="fas fa-external-link-alt"></i></a></span>
                @endif
            </div>
            <div class="card-body bg-light">
                <div class="col-12">
                    <span class="h3">Broadcast Class:</span> <span class="h4 ml-2"><span class="badge badge-light">{{ $class }}</span></span>
                    <hr>
                    <span class="h3">Broadcast Name:</span> <span class="h4 ml-2"><span class="badge badge-light">{{ $name }}</span></span>
                </div>
            </div>
        </div>
    </div>
    @foreach($broadcast as $data)
        <div class="col-12 col-xl-8 offset-xl-2 mt-3">
            <div class="card">
                <div class="card-header h3 text-info"><i class="fas fa-network-wired"></i> <pre class="d-inline">Data: </pre></div>
                <div class="card-body bg-light py-0 my-0">
                    <h4 class="mt-3 mb-0">Context:</h4>
                    <pre class="m-0">
                        <code class="language-bash">{{$data['context'] ?? 'No Context'}}</code>
                    </pre>
                    <hr>
                    <h4 class="mt-3 mb-0">Channels:</h4>
                    <pre class="m-0">
                        <code class="language-json">@json($data['channels'], JSON_PRETTY_PRINT)</code>
                    </pre>
                    <hr>
                    <h4 class="mt-3 mb-0">Payload:</h4>
                    <pre class="m-0">
                        <code class="language-json">@json($data['data'], JSON_PRETTY_PRINT)</code>
                    </pre>
                </div>
            </div>
        </div>
    @endforeach
</div>
