@section('seo')<link rel="canonical" href="{{$userViewing->slug(true)}}">
<meta name="description" content="Laravel messenger demo app by Richard Tippin. Working to release this messenger as a suite to use with laravel. Includes real-time messaging, group messaging, read receipts, video calls with screen sharing, group invitation links, and more!">
<meta name="keywords" content="laravel, demo, git, messenger, plugin, package, open, source, message, suite, php, javascript, bootstrap, framework, webrtc, tippin, jquery, MVC, ORM, OOP" />
@endsection
@section('title'){{$userViewing->name}} | {{ config('app.name') }} @endsection
