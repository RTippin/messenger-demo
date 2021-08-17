@if($status === 422)
    @include('explorer.partials.422', ['data' => $data])
@else
    @foreach($data as $type => $item)
        <h4 class="mt-3 mb-0">{{ ucfirst($type) }}:</h4>
        <pre class="m-0">
            <code class="language-json">@json($item, JSON_PRETTY_PRINT)</code>
        </pre>
    @endforeach
@endif
