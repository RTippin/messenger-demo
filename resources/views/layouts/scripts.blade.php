<script src="{{ mix('js/app.js') }}"></script>
@stack('js')
<!-- Here Tippin loads and initializes special things across the site -->
<script>
@if(messenger_profile())
    TippinManager.init({
        load : {
            NotifyManager : {
                message_popups : {{messenger_profile()->messenger->message_popups}},
                message_sound : {{messenger_profile()->messenger->message_sound}},
                call_ringtone_sound : {{messenger_profile()->messenger->call_ringtone_sound}},
                src : '{{mix("js/managers/NotifyManager.js")}}'
            },
        @stack('TippinManager-load')
        },
        common : {
            model : '{{messenger_alias()}}',
            id : '{{messenger_profile()->id}}',
            name : '{{ messenger_profile()->name }}',
            slug : '{{ messenger_profile()->avatar}}',
            mobile : {{ agent()->isMobile() ? 'true' : 'false' }},
            {{config('app.env') === 'local' ? 'debug : true' : ''}}
        },
        modules : {
            emojione : {src : '{{mix("js/modules/emojione.js")}}'},
        @stack('TippinManager-modules')

        },
        @stack('TippinManager-call')
    });
@else
    TippinManager.init({
        load : {
            GuestManager : {
                src : '{{mix("js/managers/GuestManager.js")}}'
            },
        @stack('TippinManager-load')
        },
        modules : {
        @stack('TippinManager-modules')
        },
        @stack('TippinManager-call')
    });
@endif
@if (Session::has('error_message'))
    TippinManager.alert().Alert({toast : true, theme: 'error', title : '{!! Session::get('error_message') !!}'});
@endif
@if (Session::has('info_message'))
    TippinManager.alert().Alert({toast : true, theme : 'info', title : '{!! Session::get('info_message') !!}'});
@endif
</script>
@stack('special-js')
