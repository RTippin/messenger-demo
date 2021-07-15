@foreach($data as $status => $response)
<div class="col-12 col-xl-8 offset-xl-2 mt-3">
    <div class="card">
        <div class="card-header h3 text-info"><i class="fas fa-network-wired"></i> <pre class="d-inline">{{ $verb }} {{$status}}: </pre></div>
        <div class="card-body py-0 my-0">
            @if(in_array($verb, ['GET', 'DELETE']))
                @include('explorer.get', ['data' => $response['response']])
            @endif
            @if(in_array($verb, ['POST', 'PUT', 'PATCH']))
                    @include('explorer.post', ['data' => $response, 'status' => $status])
            @endif
        </div>
    </div>
</div>
@endforeach
