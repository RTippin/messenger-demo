@extends('messenger.index')
@switch($mode)
    @case(0)
        @push('TippinManager-load')
            ThreadManager : {
            type : 0,
            setup : true,
            online_status_setting : {{messenger_profile()->messenger->online_status}},
            thread_id : '{{$thread_id}}',
            src : '{{mix("js/managers/ThreadManager.js")}}'
            },
        @endpush
    @break
    @case(3)
        @push('TippinManager-load')
            ThreadManager : {
            type : 3,
            online_status_setting : {{messenger_profile()->messenger->online_status}},
            setup : true,
            create_slug : '{{$slug}}',
            create_type : '{{$type}}',
            src : '{{mix("js/managers/ThreadManager.js")}}'
            },
        @endpush
    @break
    @case(5)
        @push('TippinManager-load')
            ThreadManager : {
            type : 5,
            online_status_setting : {{messenger_profile()->messenger->online_status}},
            setup : true,
            src : '{{mix("js/managers/ThreadManager.js")}}'
            },
        @endpush
    @break
@endswitch
@push('TippinManager-modules')
    ThreadTemplates : {src : '{{mix("js/templates/ThreadTemplates.js")}}'},
    MessengerSettings : {src : '{{mix("js/modules/MessengerSettings.js")}}'},
@endpush
