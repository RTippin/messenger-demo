@if(auth()->check() && messenger_profile()->messenger->dark_mode)
    <link id="main_css" href="{{ mix("css/dark.css") }}" rel="stylesheet">
@else
    <link id="main_css" href="{{ mix("css/app.css") }}" rel="stylesheet">
@endif