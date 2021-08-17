<div class="table-responsive">
    <table id="route_list" class="table table-sm table-hover table-striped">
        <thead>
        <tr>
            <th>Method</th>
            <th>URI</th>
            <th>NAME</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($routes as $route)
            <tr class="pointer_area">
                <td onclick="loadRoute('{{ $route['name'] }}')">{{ $route['method'] }}</td>
                <td onclick="loadRoute('{{ $route['name'] }}')">{{ $route['uri'] }}</td>
                <td onclick="loadRoute('{{ $route['name'] }}')">{{ $route['name'] }}</td>
                <td class="nowrap">
                    <button onclick="loadRoute('{{ $route['name'] }}')" class="btn btn-sm btn-primary" title="Inspect"><i class="fas fa-search"></i></button>
                    <a href="{{route('api-explorer.routes.show', $route['name'])}}" target="_blank" title="Open in new window" class="ml-2 btn btn-sm btn-warning"><i class="fas fa-external-link-alt"></i></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
