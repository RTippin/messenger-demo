<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="google-site-verification" content="KCULpGQAVEiYeRxNnbS01EdgWd4MM1hfmt29fFmZLi0" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="manifest" href="{{asset('manifest.json')}}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Tipz">
    <meta name="apple-mobile-web-app-title" content="Tipz">
    <meta name="theme-color" content="#282c30">
    <meta name="msapplication-navbutton-color" content="#282c30">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="msapplication-starturl" content="/">
    <link rel="icon" type="image/png" sizes="200x200" href="{{asset('images/tipz.png')}}">
    <link rel="apple-touch-icon" type="image/png" sizes="200x200" href="{{asset('images/tipz.png')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="title" content="@yield('title', 'Tipz Messenger - Alert')">
    @yield('seo')
    <title>@yield('title', 'Tipz Messenger - Alert')</title>
    <link href="{{ mix("css/app.css") }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.7.1/css/all.min.css">
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    @stack('css')
    @include('layouts.bgGradient')
</head>
<body>
<wrapper class="d-flex flex-column">
    <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="/">
            <img src="{{ asset('images/tipz.png') }}" width="30" height="30" class="d-inline-block align-top" alt="Tipz">
            Tipz Messenger
        </a>
    </nav>
    <div class="fixed-top mt-5 pt-3">
        <div class="container">
            <div id="alert_container"></div>
        </div>
    </div>
    <main class="py-5 mt-4 flex-fill">
        <div id="app">
            @yield('content')
        </div>
    </main>
    @include('layouts.nav.footer')
</wrapper>
<script src="{{ mix('js/app.js') }}"></script>
@stack('js')
<!-- Here we load and initialize special things across the site -->
<script>
    @if (Session::has('error_message'))
        TippinManager.alert().Modal({theme: 'danger', body : '{!! Session::get('error_message') !!}'});
    @endif
    @if (Session::has('info_message'))
        TippinManager.alert().Modal({body : '{!! Session::get('info_message') !!}'});
    @endif
    PageListeners.listen().animateLogo({elm : "#RTlog"});
</script>
@stack('special-js')
</body>
</html>
