<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="icon" type="image/png" sizes="200x200" href="{{asset('images/tipz.png')}}">
    <link rel="apple-touch-icon" type="image/png" sizes="200x200" href="{{asset('images/tipz.png')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="title" content="@yield('title', 'Tipz Messenger')">
    @yield('seo')
    <title>@yield('title', 'Tipz Messenger')</title>
    <link href="{{ mix("css/app.css") }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.7.1/css/all.min.css">
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    @stack('css')
</head>
<body>
<wrapper class="d-flex flex-column">
    <div class="fixed-top mt-2 pt-3">
        <div class="container">
            <div id="alert_container"></div>
        </div>
    </div>
    <main class="flex-fill">
        <div id="app">
            @yield('content')
        </div>
    </main>
</wrapper>
@include('layouts.scripts')
</body>
</html>
