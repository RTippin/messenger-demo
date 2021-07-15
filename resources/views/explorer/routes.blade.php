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
            <tr class="pointer_area" onclick="loadRoute('{{ $route['name'] }}')">
                <td>{{ $route['method'] }}</td>
                <td>{{ $route['uri'] }}</td>
                <td>{{ $route['name'] }}</td>
                <td><button class="btn btn-sm btn-primary nowrap">View <i class="fas fa-search"></i></button> </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
