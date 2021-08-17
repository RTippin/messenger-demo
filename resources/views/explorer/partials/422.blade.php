@foreach($data as $item)
<h4 class="mt-3 mb-0">Payload:</h4>
<pre class="m-0">
    <code class="language-json">@json($item['payload'], JSON_PRETTY_PRINT)</code>
</pre>
<h4 class="mt-3 mb-0">Response:</h4>
<pre class="m-0">
    <code class="language-json">@json($item['response'], JSON_PRETTY_PRINT)</code>
</pre>
<hr>
@endforeach
