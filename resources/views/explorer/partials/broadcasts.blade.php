<div class="table-responsive">
    <table id="broadcast_list" class="table table-sm table-hover table-striped">
        <thead>
        <tr>
            <th>BROADCAST CLASS</th>
            <th>BROADCAST AS NAME</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($broadcasts as $broadcast)
            <tr class="pointer_area">
                <td onclick="loadBroadcast('{{ $broadcast['class'] }}')">{{ $broadcast['class'] }}</td>
                <td onclick="loadBroadcast('{{ $broadcast['class'] }}')">{{ $broadcast['name'] }}</td>
                <td class="nowrap">
                    <button onclick="loadBroadcast('{{ $broadcast['class'] }}')" class="btn btn-sm btn-primary" title="Inspect"><i class="fas fa-search"></i></button>
                    <a href="{{route('api-explorer.broadcast.show', $broadcast['class'])}}" target="_blank" title="Open in new window" class="ml-2 btn btn-sm btn-warning"><i class="fas fa-external-link-alt"></i></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
