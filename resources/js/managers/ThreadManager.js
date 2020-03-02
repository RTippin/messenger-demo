window.ThreadManager = (function () {
    var opt = {
        INIT : false,
        ORIGINAL_ARG : null,
        SETUP : true,
        API : '/demo-api/messenger/',
        thread : {
            id : null,
            type : null,
            name : null,
            admin : false,
            messages_unread : false,
            click_to_read : false,
            messaging : true,
            can_call : true,
            lockout : false,
            thread_history : true,
            history_id : null,
            initializing : false,
            _id : null
        },
        states : {
            lock : true,
            load_in_retries : 0,
            state_lockout_retries : 0,
            thread_filtered : false,
            thread_filter_search : null,
            messenger_search_term : null
        },
        socket : {
            online_status_setting : 1,
            chat : null,
            socket_retries : 0,
            send_typing : 0,
            is_away : false
        },
        storage : {
            active_profiles : [],
            who_typing : [],
            threads : [],
            messages : [],
            bobble_heads : [],
            pending_messages : [],
            temp_data : null
        },
        timers : {
            recent_bobble_timeout : null,
            socket_interval : null,
            remove_typing_interval : null,
            private_bobble_refresh_timeout : null,
            bobble_refresh_interval : null,
            drag_drop_overlay_hide : null
        },
        elements : {
            nav_search_link : $(".nav-search-link"),
            my_avatar_area : $("#my_avatar_status"),
            thread_area : $("#messages_ul"),
            message_container : $("#message_container"),
            message_sidebar_container : $("#message_sidebar_container"),
            socket_error_msg : $("#socket_error"),
            thread_search_input : $("#thread_search_input"),
            thread_search_bar : $("#threads_search_bar"),
            drag_drop_zone : $('#drag_drop_overlay'),
            wb_chat_unread_count : $("#wb_chat_unread_count"),
            messenger_search_input : null,
            messenger_search_results : null,
            msg_panel : null,
            doc_file : null,
            img_file : null,
            data_table : null,
            emoji_editor : null,
            emoji : null,
            form : null,
            the_thread : null,
            msg_stack : null,
            pending_msg_stack : null,
            new_msg_alert : null
        }
    },
    mounted = {
        Initialize : function(arg) {
            if(!TippinManager.common().modules.includes('ThreadTemplates')){
                setTimeout(function () {
                    mounted.Initialize(arg)
                }, 0);
                return;
            }
            opt.states.lock = false;
            if(!opt.ORIGINAL_ARG){
                opt.ORIGINAL_ARG = arg;
            }
            if("online_status_setting" in arg) opt.socket.online_status_setting = arg.online_status_setting;
            if("messaging" in arg) opt.thread.messaging = arg.messaging;
            if("lockout" in arg) opt.thread.lockout = arg.lockout;
            if("admin" in arg) opt.thread.admin = arg.admin;
            if("can_call" in arg) opt.thread.can_call = arg.can_call;
            if("setup" in arg && "thread_id" in arg && arg.type === 0){
                mounted.setupOnce();
                LoadIn.initiate_thread({thread_id : arg.thread_id});
                return;
            }
            if("setup" in arg && arg.type === 3){
                mounted.setupOnce();
                LoadIn.createPrivate({
                    slug : arg.create_slug,
                    type : arg.create_type
                });
                return;
            }
            opt.INIT = true;
            PageListeners.listen().disposeTooltips();
            opt.thread.type = arg.type;
            if([1,2,3,4].includes(arg.type)){
                opt.elements.emoji = $("#emojionearea");
                opt.elements.form = $("#thread_form");
                opt.elements.new_msg_alert = $("#new_message_alert");
                opt.elements.msg_panel = $(".chat-body");
                opt.elements.doc_file = $("#doc_file");
                opt.elements.img_file = $("#image_file");
            }
            if([1,2,3].includes(arg.type)){
                if(arg.type === 3) opt.storage.temp_data = arg.temp_data;
                if(TippinManager.common().mobile){
                    opt.elements.emoji_editor = opt.elements.emoji;
                    opt.elements.emoji = null;
                    mounted.startWatchdog();
                }
                else{
                    if(typeof $.fn.emojioneArea !== 'undefined'){
                        mounted.loadEmoji()
                    }
                    else{
                        TippinManager.xhr().script({
                            file : '/js/modules/Emoji.js',
                            success : mounted.loadEmoji,
                            fail : function(){
                                opt.elements.emoji_editor = opt.elements.emoji;
                                opt.elements.emoji_editor.addClass('pr-special-btn');
                                opt.elements.emoji = null;
                                mounted.startWatchdog()
                            }
                        })
                    }
                }
            }
            if(arg.type === 4) mounted.startWatchdog();
            if(arg.type === 5 && !TippinManager.common().mobile) opt.elements.message_container.html(ThreadTemplates.render().empty_base());
            if(arg.type === 7){
                opt.elements.msg_panel = $(".chat-body");
                opt.elements.messenger_search_results = $("#messenger_search_content");
                opt.elements.messenger_search_input = $("#messenger_search_profiles");
                mounted.startWatchdog()
            }
            if('thread_id' in arg){
                opt.thread.id = arg.thread_id;
                opt.thread.name = arg.t_name;
                opt.elements.the_thread = $('#msg_thread_'+arg.thread_id);
                opt.elements.msg_stack = $('#messages_container_'+arg.thread_id);
                opt.elements.pending_msg_stack = $("#pending_messages");
                opt.thread.initializing = false;
                opt.thread._id = null;
                if(arg.type !== 3) methods.initializeRecentMessages();
            }
            Health.checkConnection();
            if('setup' in arg) mounted.setupOnce();
            PageListeners.listen().tooltips()
        },
        setupOnce : function(){
            if(!opt.SETUP) return;
            let elm = document.getElementById('message_container');
            LoadIn.threads();
            setInterval(function(){
                if(!NotifyManager.sockets().forced_disconnect) LoadIn.threads()
            }, 300000);
            if(opt.thread.type === 5) window.history.replaceState({type : 5}, null, '/messenger');
            window.onpopstate = function(event) {
                if(event.state && "type" in event.state && !opt.states.lock){
                    switch(event.state.type){
                        case 1:
                        case 2:
                            LoadIn.initiate_thread({thread_id : event.state.thread_id}, true);
                        break;
                        case 3:
                            LoadIn.createPrivate({slug : event.state.create_slug, type : event.state.create_type}, true);
                        break;
                        case 4:
                            LoadIn.createGroup(true);
                        break;
                        case 5:
                            LoadIn.closeOpened();
                        break;
                        case 6:
                            LoadIn.contacts(true);
                        break;
                        case 7:
                            LoadIn.search(true);
                        break;
                    }
                }
                else{
                    return false;
                }
            };
            opt.elements.thread_search_input.on("keyup mouseup", methods.checkThreadFilters);
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                elm.addEventListener(eventName, methods.fileDragDrop, false)
            });
            if(opt.elements.nav_search_link.length) opt.elements.nav_search_link.click(mounted.searchLinkClicked);
            setInterval(mounted.timeAgo, 10000);
            opt.SETUP = false;
        },
        reset : function(lock){
            mounted.stopWatchdog();
            if(opt.socket.chat) opt.socket.chat.unsubscribe();
            if(opt.timers.remove_typing_interval) clearInterval(opt.timers.remove_typing_interval);
            if(opt.timers.socket_interval) clearInterval(opt.timers.socket_interval);
            if(opt.timers.recent_bobble_timeout) clearTimeout(opt.timers.recent_bobble_timeout);
            if(opt.timers.bobble_refresh_interval) clearInterval(opt.timers.bobble_refresh_interval);
            if(opt.timers.private_bobble_refresh_timeout) clearTimeout(opt.timers.private_bobble_refresh_timeout);
            if(opt.timers.drag_drop_overlay_hide){
                clearTimeout(opt.timers.drag_drop_overlay_hide);
                opt.elements.drag_drop_zone.addClass('NS');
            }
            opt.elements.message_container.removeClass('msg-ctnr-unread');
            opt.elements.thread_area.find('.thread_list_item').removeClass('alert-warning shadow-sm rounded');
            opt.elements.thread_area.find('.thread-group-avatar').removeClass('avatar-is-online').addClass('avatar-is-offline');
            PageListeners.listen().disposeTooltips();
            opt = Object.assign({}, opt, {
                thread : {
                    id : null,
                    type : null,
                    name : null,
                    admin : false,
                    messages_unread : false,
                    click_to_read : false,
                    messaging : true,
                    can_call : true,
                    lockout : false,
                    thread_history : true,
                    history_id : null,
                    initializing : false,
                    _id : null
                },
                states : {
                    lock : lock,
                    load_in_retries : 0,
                    state_lockout_retries : 0,
                    thread_filtered : opt.states.thread_filtered,
                    thread_filter_search : opt.states.thread_filter_search,
                    messenger_search_term : null
                },
                socket : {
                    online_status_setting : opt.socket.online_status_setting,
                    chat : null,
                    socket_retries : 0,
                    send_typing : 0,
                    is_away : false
                },
                storage : {
                    active_profiles : [],
                    who_typing : [],
                    threads : opt.storage.threads,
                    messages : [],
                    bobble_heads : [],
                    pending_messages : [],
                    temp_data : null
                },
                timers : {
                    recent_bobble_timeout : null,
                    socket_interval : null,
                    remove_typing_interval : null,
                    private_bobble_refresh_timeout : null,
                    bobble_refresh_interval : null,
                    drag_drop_overlay_hide : null
                },
                elements : {
                    nav_search_link : opt.elements.nav_search_link,
                    my_avatar_area : opt.elements.my_avatar_area,
                    thread_area : opt.elements.thread_area,
                    message_container : opt.elements.message_container,
                    message_sidebar_container : opt.elements.message_sidebar_container,
                    socket_error_msg : opt.elements.socket_error_msg,
                    thread_search_input : opt.elements.thread_search_input,
                    thread_search_bar : opt.elements.thread_search_bar,
                    drag_drop_zone : opt.elements.drag_drop_zone,
                    wb_chat_unread_count : opt.elements.wb_chat_unread_count,
                    messenger_search_input : null,
                    messenger_search_results : null,
                    msg_panel : null,
                    doc_file : null,
                    img_file : null,
                    data_table : null,
                    emoji_editor : null,
                    emoji : null,
                    form : null,
                    the_thread : null,
                    msg_stack : null,
                    pending_msg_stack : null,
                    new_msg_alert : null
                }
            })
        },
        loadEmoji : function(){
            opt.elements.emoji.emojioneArea({
                pickerPosition: "top",
                saveEmojisAs : "shortname",
                filtersPosition: "bottom",
                search: false,
                tonesStyle: "radio",
                autocomplete: false,
                events: {
                    ready : function(){
                        if(!opt.thread.lockout && (opt.thread.messaging || opt.thread.admin)) this.enable();
                        document.getElementsByClassName('emojionearea-editor')[0].id = 'emoji_input_area';
                        opt.elements.emoji_editor = $('.emojionearea-editor');
                        mounted.startWatchdog()
                    }
                },
                attributes: {
                    spellcheck : true
                }
            });
        },
        timeAgo : function(){
            $("time.timeago").each(function () {
                $(this).html(TippinManager.format().makeTimeAgo($(this).attr('datetime')))
            });
        },
        startWatchdog : function(){
            switch(opt.thread.type){
                case 1:
                case 2:
                    if(!opt.thread.lockout && (opt.thread.messaging || opt.thread.admin)) opt.elements.emoji_editor.prop('disabled', false);
                    opt.timers.remove_typing_interval = setInterval(methods.removeTypers, 1000);
                    opt.timers.bobble_refresh_interval = setInterval(function() {
                        if(!opt.storage.active_profiles.length) LoadIn.bobbleHeads()
                    }, 120000);
                    opt.elements.msg_panel.click(mounted.msgPanelClick);
                    opt.elements.msg_panel.scroll(mounted.msgPanelScroll);
                    opt.elements.img_file.change(mounted.imageChange);
                    opt.elements.doc_file.change(mounted.documentChange);
                    opt.elements.emoji_editor.on('paste', methods.pasteImage);
                    opt.elements.form.keydown(mounted.formKeydown);
                    opt.elements.form.on('input keyup', methods.manageSendMessageButton);
                    opt.elements.form.on('submit', mounted.stopDefault);
                    opt.elements.new_msg_alert.click(mounted.newMsgAlertClick);
                    opt.elements.message_container.click(mounted.clickMarkRead);
                    if(TippinManager.common().mobile) opt.elements.emoji_editor.click(mounted.inputClickScroll);
                    if(!TippinManager.common().mobile) opt.elements.emoji_editor.focus();
                break;
                case 3:
                    opt.elements.emoji_editor.prop('disabled', false);
                    opt.elements.msg_panel.click(mounted.msgPanelClick);
                    opt.elements.img_file.change(mounted.imageChange);
                    opt.elements.doc_file.change(mounted.documentChange);
                    opt.elements.form.keydown(mounted.formKeydown);
                    opt.elements.form.on('input keyup', methods.manageSendMessageButton);
                    opt.elements.form.on('submit', mounted.stopDefault);
                    if(!TippinManager.common().mobile) opt.elements.emoji_editor.focus();
                break;
                case 4:
                    let subject = document.getElementById('subject');
                    opt.elements.msg_panel.click(mounted.msgPanelClick);
                    if(!TippinManager.common().mobile) subject.focus();
                    PageListeners.listen().validateForms();
                break;
                case 7:
                    opt.elements.messenger_search_input.on("keyup mouseup", mounted.runMessengerSearch);
                    opt.elements.msg_panel.click(mounted.msgPanelClick);
                    opt.elements.messenger_search_input.focus();
                break;
            }
        },
        stopWatchdog : function(){
            switch(opt.thread.type){
                case 1:
                case 2:
                    try{
                        opt.elements.msg_panel.off('click', mounted.msgPanelClick);
                        opt.elements.msg_panel.off('scroll', mounted.msgPanelScroll);
                        opt.elements.img_file.off('change', mounted.imageChange);
                        opt.elements.doc_file.off('change', mounted.documentChange);
                        opt.elements.emoji_editor.off('paste', methods.pasteImage);
                        opt.elements.form.off('keydown', mounted.formKeydown);
                        opt.elements.form.off('input keyup', methods.manageSendMessageButton);
                        opt.elements.form.off('submit', mounted.stopDefault);
                        opt.elements.new_msg_alert.off('click', mounted.newMsgAlertClick);
                        opt.elements.message_container.off('click', mounted.clickMarkRead);
                        if(TippinManager.common().mobile) opt.elements.emoji_editor.off('click', mounted.inputClickScroll);
                    }catch (e) {
                        console.log(e);
                    }
                break;
                case 3:
                    try{
                        opt.elements.msg_panel.off('click', mounted.msgPanelClick);
                        opt.elements.img_file.off('change', mounted.imageChange);
                        opt.elements.doc_file.off('change', mounted.documentChange);
                        opt.elements.form.off('keydown', mounted.formKeydown);
                        opt.elements.form.off('input keyup', methods.manageSendMessageButton);
                        opt.elements.form.off('submit', mounted.stopDefault);
                    }catch (e) {
                        console.log(e);
                    }
                break;
                case 4:
                    try{
                        opt.elements.msg_panel.off('click', mounted.msgPanelClick);
                    }catch (e) {
                        console.log(e);
                    }
                break;
                case 7:
                    try{
                        opt.elements.msg_panel.off('click', mounted.msgPanelClick);
                        opt.elements.messenger_search_input.off("keyup mouseup", mounted.runMessengerSearch);
                    }catch (e) {
                        console.log(e);
                    }
                break;
            }
        },
        stopDefault : function(e){
            e.preventDefault()
        },
        searchLinkClicked : function(e){
            mounted.stopDefault(e);
            $('body').click();
            LoadIn.search()
        },
        runMessengerSearch : function(e){
            if(opt.thread.type !== 7) return;
            let current_term = opt.states.messenger_search_term, time = new Date();
            if(e && e.type === 'mouseup'){
                setTimeout(mounted.runMessengerSearch, 0);
                return;
            }
            if(opt.elements.messenger_search_input.val().trim().length){
                if(opt.elements.messenger_search_input.val().trim().length >= 3){
                    if(current_term !== opt.elements.messenger_search_input.val().trim()){
                        opt.states.messenger_search_term = opt.elements.messenger_search_input.val().trim();
                        opt.elements.messenger_search_results.html(ThreadTemplates.render().loader());
                        TippinManager.xhr().request({
                            route : opt.API+'search/'+opt.states.messenger_search_term,
                            success : methods.manageMessengerSearch,
                            fail_alert : true
                        })
                    }
                }
                else{
                    opt.states.messenger_search_term = opt.elements.messenger_search_input.val().trim();
                    opt.elements.messenger_search_results.html(ThreadTemplates.render().thread_empty_search(true));
                }
            }
            else{
                opt.states.messenger_search_term = null;
                opt.elements.messenger_search_results.html(ThreadTemplates.render().thread_empty_search());
            }
        },
        inputClickScroll : function(){
            setTimeout(function () {
                methods.threadScrollBottom(true, false)
            }, 200)
        },
        formKeydown : function(e){
            switch (opt.thread.type) {
                case 1:
                case 2:
                    if(opt.thread.lockout || (!opt.thread.messaging && !opt.thread.admin)) return;
                    if (e.keyCode === 13) {
                        methods.sendMessage();
                        methods.stopTyping();
                        return;
                    }
                    methods.isTyping();
                break;
                case 3:
                    if(e.keyCode === 13) new_forms.newPrivate(0);
                break;
            }
        },
        clickMarkRead : function(){
            if(opt.thread.click_to_read || methods.checkThreadStorageUnread()) methods.markRead()
        },
        msgPanelClick : function(e){
            if(opt.thread.type === 7){
                let focus_input = document.getElementById('messenger_search_profiles');
                TippinManager.format().focusEnd(focus_input, false);
                return;
            }
            let focus_input = (opt.elements.emoji ? document.getElementById('emoji_input_area') : document.getElementById('emojionearea'));
            switch (opt.thread.type) {
                case 1:
                case 2:
                    if(opt.thread.lockout || (!opt.thread.messaging && !opt.thread.admin)) return;
                    let elm_class = $(e.target).attr('class');
                    if (elm_class === 'message-text' || elm_class === 'message-text pt-2' || elm_class === 'fas fa-trash' || TippinManager.common().mobile) return;
                    TippinManager.format().focusEnd(focus_input, !!opt.elements.emoji);
                break;
                case 3:
                    TippinManager.format().focusEnd(focus_input, !!opt.elements.emoji);
                break;
                case 4:
                    if(e.target.id === 'msg_thread_new_group') TippinManager.format().focusEnd(document.getElementById('subject'), false);
                break;
            }
        },
        msgPanelScroll : function(){
            if($(this).scrollTop()  <= 500 ) methods.loadHistory();
            if(methods.threadScrollBottom(false, true) && opt.thread.messages_unread && !opt.socket.is_away && document.hasFocus()) methods.markRead()
        },
        newMsgAlertClick : function(){
            methods.threadScrollBottom(true, false);
            methods.markRead()
        },
        imageChange : function(){
            switch (opt.thread.type) {
                case 1:
                case 2:
                    if(opt.thread.lockout || (!opt.thread.messaging && !opt.thread.admin)) return;
                    let input = document.getElementById('image_file'), files = input.files;
                    ([...files]).forEach(methods.sendUploadFiles);
                    input.value = '';
                break;
                case 3:
                    TippinManager.button().addLoader({id : '#image_upload_btn'});
                    new_forms.newPrivate(1);
                break;
            }
        },
        documentChange : function(){
            switch (opt.thread.type) {
                case 1:
                case 2:
                    if(opt.thread.lockout || (!opt.thread.messaging && !opt.thread.admin)) return;
                    let input = document.getElementById('doc_file'), files = input.files;
                    ([...files]).forEach(methods.sendUploadFiles);
                    input.value = '';
                break;
                case 3:
                    TippinManager.button().addLoader({id : '#file_upload_btn'});
                    new_forms.newPrivate(2);
                break;
            }
        },
        avatarListener : function(){
            $('.grp-img-check').click(function() {
                $('.grp-img-check').not(this).removeClass('grp-img-checked').siblings('input').prop('checked',false);
                $(this).addClass('grp-img-checked').siblings('input').prop('checked',true);
            });
            $("#avatar_image_file").change(function(){
                groups.updateGroupAvatar({action : 'upload'});
            });
        },
        switchToggleListener : function(){
            $(".switch_input").each(function(){
                if($(this).is(':checked')){
                    $(this).parents().closest('tr').addClass('table-warning');
                    return;
                }
                $(this).parents().closest('tr').removeClass('table-warning')
            })
        },
        startPresence : function(full){
            if(full) opt.socket.chat = null;
            if(opt.socket.chat){
                opt.socket.chat.subscribe();
                return;
            }
            if(typeof NotifyManager.sockets().Echo.connector.channels['presence-thread_'+opt.thread.id] !== 'undefined'){
                NotifyManager.sockets().Echo.connector.channels['presence-thread_'+opt.thread.id].subscribe();
                opt.socket.chat = NotifyManager.sockets().Echo.connector.channels['presence-thread_'+opt.thread.id]
            }
            else{
                opt.socket.chat = NotifyManager.sockets().Echo.join('thread_'+opt.thread.id);
            }
            opt.socket.chat.here(function(users){
                opt.storage.active_profiles = [];
                $('.thread_error_area').hide();
                $.each(users, function() {
                    if(this.owner_id !== TippinManager.common().id){
                        opt.storage.active_profiles.push({
                            owner_id : this.owner_id,
                            slug : this.slug,
                            name : this.name,
                            online : this.online
                        });
                        methods.updateBobbleHead(this.owner_id, null)
                    }
                });
                methods.drawBobbleHeads();
                methods.sendOnlineStatus((opt.socket.is_away && opt.socket.online_status_setting !== 0 ? 2 : opt.socket.online_status_setting));
            })
            .joining(function(user) {
                opt.storage.active_profiles.push({
                    owner_id : user.owner_id,
                    slug : user.slug,
                    name : user.name,
                    online : user.online
                });
                if(opt.storage.messages.length) methods.updateBobbleHead(user.owner_id, opt.storage.messages[(opt.storage.messages.length-1)].message_id);
                methods.drawBobbleHeads();
                methods.sendOnlineStatus((opt.socket.is_away && opt.socket.online_status_setting !== 0 ? 2 : opt.socket.online_status_setting));
                PageListeners.listen().tooltips()
            })
            .leaving(function(user) {
                methods.updateActiveProfile(user.owner_id, 3)
            })
            .listenForWhisper('typing', function(user){
                if(!opt.storage.messages.length) return;
                if(!user.typing){
                    methods.removeTypers(user.owner_id);
                    return;
                }
                let time = new Date(),
                found = opt.storage.who_typing.filter( function(el) {
                    return el.includes( user.owner_id );
                });
                if(!found.length){
                    opt.storage.who_typing.push([user.owner_id, user.name, time.getTime()]);
                    methods.addTypers();
                    return;
                }
                found[0][2] = time.getTime();
            })
            .listenForWhisper('online', function(user){
                methods.threadOnlineStatus((user.online));
                methods.updateActiveProfile(user.owner_id, user.online)
            })
            .listenForWhisper('read', function(message){
                methods.updateBobbleHead(message.owner_id, message.message_id);
                methods.drawBobbleHeads()
            })
            .listenForWhisper('send_message_setting', function (arg) {
                methods.messageFormState(arg.send_message)
            })
        }
    },
    Health = {
        checkConnection : function(){
            if(!TippinManager.common().modules.includes('NotifyManager') || !NotifyManager.sockets().status || !NotifyManager.sockets().Echo){
                if(opt.socket.socket_retries >= 5){
                    opt.storage.active_profiles = [];
                    opt.socket.socket_retries = 0;
                    Health.unreadCheck();
                    opt.elements.socket_error_msg.html(ThreadTemplates.render().socket_error());
                    if(opt.thread.id){
                        $('.thread_error_area').show();
                        $('.thread_error_btn').popover()
                    }
                }
                if(opt.timers.socket_interval === null){
                    opt.timers.socket_interval = setInterval(function() {
                        Health.checkConnection();
                    }, 1000);
                }
                opt.socket.socket_retries++;
                if(TippinManager.common().modules.includes('NotifyManager') && NotifyManager.sockets().forced_disconnect) opt.elements.my_avatar_area.html(ThreadTemplates.render().my_avatar_status(0));
                return;
            }
            Health.onConnection()
        },
        onConnection : function(full){
            opt.elements.my_avatar_area.html(ThreadTemplates.render().my_avatar_status(opt.socket.online_status_setting));
            PageListeners.listen().tooltips();
            opt.socket.socket_retries = 0;
            opt.elements.socket_error_msg.html('');
            clearInterval(opt.timers.socket_interval);
            opt.timers.socket_interval = null;
            if(opt.thread.id && opt.thread.type !== 3){
                $('.thread_error_area').hide();
                mounted.startPresence(full)
            }
        },
        reConnected : function(full){
            opt.elements.my_avatar_area.html(ThreadTemplates.render().my_avatar_status(opt.socket.online_status_setting));
            PageListeners.listen().tooltips();
            Health.onConnection(full);
            LoadIn.threads();
            if(opt.thread.id){
                opt.storage.bobble_heads = [];
                methods.initializeRecentMessages(true);
            }
        },
        unreadCheck : function(){
            if(!TippinManager.common().modules.includes('NotifyManager') || NotifyManager.sockets().forced_disconnect) return;
            let checkTotalUnread = function () {
                TippinManager.xhr().request({
                    route : opt.API+'get/unread_count',
                    success : function(data){
                        if(NotifyManager.counts().threads !== data.total_unread){
                            NotifyManager.updateMessageCount({total_unread : data.total_unread});
                            LoadIn.threads()
                        }
                    },
                    fail : null
                })
            };
            if(opt.thread.id){
                TippinManager.xhr().request({
                    route : opt.API+'get/'+opt.thread.id+'/is_unread',
                    success : function(data){
                        if(data.unread){
                            opt.storage.bobble_heads = [];
                            if(document.hasFocus() && !opt.socket.is_away) methods.markRead();
                            methods.initializeRecentMessages(true);
                            LoadIn.thread(opt.thread.id);
                            if(!document.hasFocus() || opt.socket.is_away) NotifyManager.sound('message');
                        }
                        else{
                            checkTotalUnread()
                        }
                    },
                    fail : null
                });
                return;
            }
            checkTotalUnread()
        }
    },
    Imports = {
        newMessage : function(data){
            if(opt.thread.id === data.thread_id){
                methods.addMessage(data);
                return;
            }
            if(opt.thread.initializing && opt.thread._id === data.thread_id){
                opt.storage.pending_messages.push(data);
                methods.updateThread(data, false, false, false, true);
                return;
            }
            methods.updateThread(data, false, false, false, true);
            if(TippinManager.common().id !== data.owner_id) NotifyManager.sound('message')
        },
        callStatus : function(data){
            methods.threadCallStatus(data)
        },
        addedToGroup : function(thread_id){
            LoadIn.thread(thread_id);
            NotifyManager.sound('message')
        },
        removedFromGroup : function(thread_id){
            if(opt.thread.id === thread_id) LoadIn.closeOpened();
            setTimeout(function () {
                methods.removeThread(thread_id)
            }, 2500)
        },
        purgeMessage : function(message){
            if(opt.thread.id === message.thread_id){
                methods.purgeMessage(message.message_id);
                $("#message_"+message.message_id).remove()
            }
        }
    },
    methods = {
        initiatePrivate : function(arg, data, noHistory){
            opt.storage.bobble_heads = data.bobble_heads;
            opt.storage.messages = data.recent_messages;
            opt.elements.message_container.html(ThreadTemplates.render().render_private(data.thread, data.party));
            if(!noHistory) window.history.pushState({type : 1, thread_id : data.thread.thread_id}, null, '/messenger/'+data.thread.thread_id);
            mounted.Initialize({
                type : data.thread.thread_type,
                thread_id : data.thread.thread_id,
                t_name : data.thread.name,
                can_call : data.party.can_call,
                lockout : data.thread.options.lockout
            });
            methods.updateThread(data.thread, true, false, false, ('new' in arg))
        },
        initiateGroup : function(arg, data, noHistory){
            opt.storage.bobble_heads = data.bobble_heads;
            opt.storage.messages = data.recent_messages;
            opt.elements.message_container.html(ThreadTemplates.render().render_group(data.thread));
            if(!noHistory) window.history.pushState({type : 2, thread_id : data.thread.thread_id}, null, '/messenger/'+data.thread.thread_id);
            mounted.Initialize({
                type : data.thread.thread_type,
                thread_id : data.thread.thread_id,
                t_name : data.thread.name,
                admin : data.thread.options.admin,
                messaging : data.thread.options.send_message,
                lockout : data.thread.options.lockout
            });
            methods.updateThread(data.thread, true, false, false, ('new' in arg))
        },
        manageMessengerSearch : function(search){
            if(opt.thread.type !== 7) return;
            if(!search.results.length){
                opt.elements.messenger_search_results.html(ThreadTemplates.render().thread_empty_search(true, true));
                return;
            }
            opt.elements.messenger_search_results.html('');
            search.results.forEach((profile) => {
                opt.elements.messenger_search_results.append(ThreadTemplates.render().messenger_search(profile))
            })
        },
        fileDragDrop : function(e){
            let isFile = function () {
                for (let i = 0; i < e.dataTransfer.items.length; i++){
                    if (e.dataTransfer.items[i].kind === "file") {
                        return true;
                    }
                }
                return false;
            };
            if(!isFile()) return;
            e.preventDefault();
            e.stopPropagation();
            if(![1,2].includes(opt.thread.type) || !opt.thread.id || opt.thread.lockout || (!opt.thread.messaging && !opt.thread.admin)) return;
            if(['dragenter', 'dragover'].includes(e.type)){
                if(opt.timers.drag_drop_overlay_hide) clearTimeout(opt.timers.drag_drop_overlay_hide);
                opt.elements.drag_drop_zone.fadeIn('fast');
            }
            if(e.type === 'dragleave'){
                opt.timers.drag_drop_overlay_hide = setTimeout(function () {
                    opt.elements.drag_drop_zone.fadeOut('fast')
                }, 200);
            }
            if(e.type === 'drop'){
                opt.elements.drag_drop_zone.fadeOut('fast');
                let files = e.dataTransfer.files;
                ([...files]).forEach(methods.sendUploadFiles);
                opt.elements.emoji_editor.focus()
            }
        },
        manageSendMessageButton : function(){
            let btn = $("#inline_send_msg_btn"), message_contents = (opt.elements.emoji ? opt.elements.emoji.data("emojioneArea").getText() : opt.elements.emoji_editor.val());
            if(message_contents.trim().length){
                if(!btn.length){
                    opt.elements.emoji ? $(".emojionearea-editor").after(ThreadTemplates.render().send_msg_btn(true)) : opt.elements.emoji_editor.after(ThreadTemplates.render().send_msg_btn(false))
                }
            }
            else{
                btn.remove()
            }
        },
        messageFormState : function(power){
            if(opt.thread.admin) return;
            if(power && !opt.thread.messaging){
                opt.thread.messaging = true;
                $("#messaging_disabled_overlay").remove();
                opt.elements.emoji_editor.prop('disabled', false).focus();
                if(opt.elements.emoji) opt.elements.emoji[0].emojioneArea.enable()
            }
            if(!power && opt.thread.messaging){
                opt.thread.messaging = false;
                $(".chat-footer").prepend(ThreadTemplates.render().messages_disabled_overlay());
                opt.elements.emoji_editor.prop('disabled', true);
                if(opt.elements.emoji) opt.elements.emoji[0].emojioneArea.disable()
            }
        },
        threadScrollBottom : function(force, check){
            if(!opt.elements.the_thread) return false;
            let top = opt.elements.the_thread.prop("scrollTop"), height = opt.elements.the_thread.prop("scrollHeight"), offset = opt.elements.the_thread.prop("offsetHeight");
            if(force || top === (height - offset) || ((height - offset) - top) < 200){
                if(!check) opt.elements.the_thread.scrollTop(height);
                return true;
            }
            return false;
        },
        statusOnline : function(state, inactivity){
            opt.socket.is_away = (state === 2 && inactivity);
            if(opt.INIT && opt.elements.my_avatar_area.length){
                opt.elements.my_avatar_area.html(ThreadTemplates.render().my_avatar_status((state === 1 && opt.socket.online_status_setting === 2 ? 2 : (state === 1 && opt.socket.online_status_setting === 0 ? 0 : (state === 2 && opt.socket.online_status_setting === 0 ? 0 : state)))));
                PageListeners.listen().tooltips();
            }
            methods.sendOnlineStatus((state === 1 && opt.socket.online_status_setting === 2 ? 2 : state))
        },
        updateOnlineStatusSetting : function(state){
            opt.socket.online_status_setting = state;
            methods.statusOnline(state, false)
        },
        checkThreadStorageUnread : function(){
            if(!opt.thread.id) return false;
            let thread = methods.locateStorageItem({type : 'thread', id : opt.thread.id});
            return thread.found && opt.storage.threads[thread.index].unread;
        },
        markRead : function(){
            if(!opt.thread.id || !methods.threadScrollBottom(false, true)) return;
            opt.thread.messages_unread = false;
            opt.elements.message_container.removeClass('msg-ctnr-unread');
            opt.thread.click_to_read = false;
            opt.elements.new_msg_alert.hide();
            methods.updateThread({thread_id : opt.thread.id}, false, false, true, false);
            if(opt.storage.messages.length) methods.seenMessage(opt.storage.messages[(opt.storage.messages.length-1)].message_id);
            TippinManager.xhr().request({
                route : opt.API+'get/'+opt.thread.id+'/mark_read',
                fail : null
            })
        },
        loadDataTable : function(elm, special){
            if(opt.elements.data_table) opt.elements.data_table.destroy();
            if(!elm || !elm.length) return;
            if(special){
                opt.elements.data_table = elm.DataTable({
                    "language": {
                        "info": "Showing _START_ to _END_ of _TOTAL_ friends",
                        "lengthMenu": "Show _MENU_ friends",
                        "infoEmpty": "Showing 0 to 0 of 0 friends",
                        "infoFiltered": "(filtered from _MAX_ total friends)",
                        "emptyTable": "No friends found",
                        "zeroRecords": "No matching friends found"
                    },
                    "drawCallback": function(settings){
                        let api = new $.fn.DataTable.Api(settings), pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');
                        pagination.toggle(api.page.info().pages > 1);
                    },
                    "pageLength": 100
                });
                return;
            }
            opt.elements.data_table = elm.DataTable({
                "language": {
                    "info": "Showing _START_ to _END_ of _TOTAL_ participants",
                    "lengthMenu": "Show _MENU_ participants",
                    "infoEmpty": "Showing 0 to 0 of 0 participants",
                    "infoFiltered": "(filtered from _MAX_ total participants)",
                    "emptyTable": "No participants found",
                    "zeroRecords": "No matching participants found"
                },
                "drawCallback": function(settings){
                    let api = new $.fn.DataTable.Api(settings), pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');
                    pagination.toggle(api.page.info().pages > 1);
                }
            });
        },
        addTypers : function(){
            $.each(opt.storage.who_typing, function() {
                if(!$("#typing_"+this[0]).length){
                    methods.updateBobbleHead(this[0], null)
                }
            });
            methods.drawBobbleHeads()
        },
        removeTypers : function(x){
            x = x || null;
            let time = new Date();
            if(x){
                opt.storage.who_typing.splice( $.inArray(x, opt.storage.who_typing), 1);
                methods.updateBobbleHead(x, opt.storage.messages[(opt.storage.messages.length-1)].message_id);
                methods.drawBobbleHeads();
                return;
            }
            if(opt.storage.who_typing.length){
                $.each(opt.storage.who_typing, function() {
                    if(((time.getTime() - this[2]) / 1000) > 2){
                        opt.storage.who_typing.splice( $.inArray(this[0], opt.storage.who_typing), 1);
                        methods.updateBobbleHead(this[0], opt.storage.messages[(opt.storage.messages.length-1)].message_id);
                    }
                });
                methods.drawBobbleHeads();
                return;
            }
            $('.typing-ellipsis').remove()
        },
        purgeMessage : function(id){
            let message = methods.locateStorageItem({type : 'message', id : id}), i = message.index;
            if (message.found){
                if(i === 0 && opt.storage.messages.length > 1 && opt.storage.messages[i+1].owner_id === opt.storage.messages[i].owner_id){
                    opt.elements.the_thread.find("#message_"+opt.storage.messages[i+1].message_id).replaceWith(
                        (opt.storage.messages[i+1].owner_id === TippinManager.common().id ? ThreadTemplates.render().my_message(opt.storage.messages[i+1]) : ThreadTemplates.render().message(opt.storage.messages[i+1]))
                    )
                }
                else if(opt.storage.messages.length-1 >= i+1 && opt.storage.messages[i-1].owner_id !== opt.storage.messages[i].owner_id && opt.storage.messages[i+1].owner_id === opt.storage.messages[i].owner_id){
                    opt.elements.the_thread.find("#message_"+opt.storage.messages[i+1].message_id).replaceWith(
                        (opt.storage.messages[i+1].owner_id === TippinManager.common().id ? ThreadTemplates.render().my_message(opt.storage.messages[i+1]) : ThreadTemplates.render().message(opt.storage.messages[i+1]))
                    )
                }
                else if(opt.storage.messages.length-1 >= i+1 && ![0,1,2].includes(opt.storage.messages[i-1].message_type) && opt.storage.messages[i+1].owner_id === opt.storage.messages[i].owner_id){
                    opt.elements.the_thread.find("#message_"+opt.storage.messages[i+1].message_id).replaceWith(
                        (opt.storage.messages[i+1].owner_id === TippinManager.common().id ? ThreadTemplates.render().my_message(opt.storage.messages[i+1]) : ThreadTemplates.render().message(opt.storage.messages[i+1]))
                    )
                }
                opt.storage.messages.splice(i, 1);
            }
            methods.imageLoadListener(false);
        },
        privateMainBobbleDraw : function(id){
            let bobble = methods.locateStorageItem({type : 'bobble', id : id}),
            status = $("#main_bobble_"+id);
            if(!status.length || !bobble.found) return;
            status.html(ThreadTemplates.render().thread_private_header_bobble(opt.storage.bobble_heads[bobble.index]));
            PageListeners.listen().tooltips();
            clearTimeout(opt.timers.private_bobble_refresh_timeout);
            if(opt.storage.bobble_heads[bobble.index].online === 0){
                opt.timers.private_bobble_refresh_timeout = setTimeout(function(){
                    methods.privateMainBobbleDraw(id)
                }, 20000)
            }
        },
        drawBobbleHeads : function(){
            if(!opt.storage.bobble_heads.length) return;
            opt.storage.bobble_heads.forEach(function(value){
                if(!value.message_id || 'added' in value && value.added) return;
                $(".bobble_head_"+value.owner_id).remove();
                let message = $("#message_"+value.message_id);
                if((value.caught_up && value.typing) || (opt.storage.messages[(opt.storage.messages.length-1)].message_id === value.message_id)){
                    $("#seen-by_final").prepend(ThreadTemplates.render().bobble_head(value, true));
                    value.added = true;
                    value.caught_up = true
                }
                else if(message.length){
                    if(!message.next().hasClass('seen-by')) message.after(ThreadTemplates.render().seen_by(value));
                    $("#seen-by_"+value.message_id).prepend(ThreadTemplates.render().bobble_head(value, false));
                    value.added = true;
                    value.caught_up = false
                }
                if(opt.thread.type === 1){
                    methods.privateMainBobbleDraw(value.owner_id)
                }
            });
            $(".seen-by").each(function(){
                if(!$(this).children().length) $(this).remove()
            });
            methods.threadScrollBottom(false, false)
        },
        updateBobbleHead : function(owner, message){
            let typing = opt.storage.who_typing.filter( function(el) {
                return el.includes(owner);
            }),
            found = false;
            if(message === null){
                if(typing.length && opt.storage.messages.length){
                    message = opt.storage.messages[(opt.storage.messages.length-1)].message_id
                }
                else{
                    message = false
                }
            }
            for(let x = 0; x < opt.storage.active_profiles.length; x++) {
                if (opt.storage.active_profiles[x].owner_id === owner){
                    found = opt.storage.active_profiles[x];
                    break;
                }
            }
            let bobble = methods.locateStorageItem({type : 'bobble', id : owner}), i = bobble.index;
            if (bobble.found){
                opt.storage.bobble_heads[i].message_id = (message ? message : opt.storage.bobble_heads[i].message_id);
                opt.storage.bobble_heads[i].added = false;
                opt.storage.bobble_heads[i].typing = !!typing.length;
                opt.storage.bobble_heads[i].caught_up = (typing.length ? true : opt.storage.bobble_heads[i].caught_up);
                opt.storage.bobble_heads[i].in_chat = (found || !found && !!typing.length);
                if(found){
                    opt.storage.bobble_heads[i].online = found.online;
                }
                else if(!found && !!typing.length){
                    opt.storage.bobble_heads[i].online = 1;
                }
                $(".bobble_head_"+owner).remove();
                $(".seen-by").each(function(){
                    if(!$(this).children().length) $(this).remove()
                });
            }
        },
        checkRecentBobbleHeads : function(reload){
            if(reload){
                LoadIn.bobbleHeads();
                return;
            }
            for(let i = 0; i < opt.storage.bobble_heads.length; i++) {
                if (opt.storage.bobble_heads[i].caught_up && !opt.storage.bobble_heads[i].typing && opt.storage.messages[(opt.storage.messages.length-1)].message_id !== opt.storage.bobble_heads[i].message_id){
                    methods.updateBobbleHead(opt.storage.bobble_heads[i].owner_id, opt.storage.bobble_heads[i].message_id)
                }
            }
            methods.drawBobbleHeads()
        },
        updateActiveProfile : function(owner, action){
            for(let i = 0; i < opt.storage.active_profiles.length; i++) {
                if (opt.storage.active_profiles[i].owner_id === owner){
                    if(action === 3){
                        opt.storage.active_profiles.splice(i, 1);
                    }
                    else{
                        opt.storage.active_profiles[i].online = action;
                    }
                    break;
                }
            }
            if(action === 3 && opt.thread.type === 1){
                LoadIn.bobbleHeads();
                return;
            }
            methods.updateBobbleHead(owner, null);
            methods.drawBobbleHeads()
        },
        imageLoadListener : function(scroll){
            let images = document.getElementsByClassName('msg_image'),
            emojis = document.getElementsByClassName('emojione'),
            loadImage = function (e) {
                $(e.target).siblings('.spinner-border').remove();
                $(e.target).removeClass('msg_image NS');
                if(scroll) methods.threadScrollBottom(true, false);
                if(e.type === 'error') e.target.src = '/images/image404.png';
            },
            loadEmoji = function (e) {
                if(scroll) methods.threadScrollBottom(true, false);
                if(e.type === 'error') $(e.target).remove()
            };
            [].forEach.call( images, function( img ) {
                img.addEventListener( 'load', loadImage, false );
                img.addEventListener( 'error', loadImage, false );
            });
            [].forEach.call( emojis, function( img ) {
                img.addEventListener( 'load', loadEmoji, false );
                img.addEventListener( 'error', loadEmoji, false );
            });
        },
        manageRecentMessages : function(){
            opt.storage.messages.forEach(function(value, key){
                if('added' in value) return;
                value.added = true;
                if(![0,1,2].includes(value.message_type)){
                    opt.elements.msg_stack.append(ThreadTemplates.render().system_message(value));
                    return;
                }
                if(value.owner_id === TippinManager.common().id){
                    if(key !== 0
                        && opt.storage.messages[key-1].owner_id === value.owner_id
                        && [0,1,2].includes(opt.storage.messages[key-1].message_type)
                        && TippinManager.format().timeDiffInUnit(value.created_at, opt.storage.messages[key-1].created_at, 'minutes') < 30
                    ){
                        opt.elements.msg_stack.append(ThreadTemplates.render().my_message_grouped(value));
                        return;
                    }
                    opt.elements.msg_stack.append(ThreadTemplates.render().my_message(value));
                    return;
                }
                if(key !== 0
                    && opt.storage.messages[key-1].owner_id === value.owner_id
                    && [0,1,2].includes(opt.storage.messages[key-1].message_type)
                    && TippinManager.format().timeDiffInUnit(value.created_at, opt.storage.messages[key-1].created_at, 'minutes') < 30
                ){
                    opt.elements.msg_stack.append(ThreadTemplates.render().message_grouped(value));
                    return;
                }
                opt.elements.msg_stack.append(ThreadTemplates.render().message(value))
            });
            methods.imageLoadListener(true);
            methods.drawBobbleHeads();
            methods.threadScrollBottom(true, false);
        },
        manageHistoryMessages : function(data){
            $("#loading_history_marker").remove();
            let messages = data.messages.reverse();
            messages.forEach(function(value){
                if(!methods.locateStorageItem({type : 'message', id :value.message_id }).found){
                    opt.storage.messages.unshift(value)
                }
            });
            opt.storage.messages.reverse().forEach(function(value, key){
                if('added' in value) return;
                value.added = true;
                if(value.message_type !== null && ![0,1,2].includes(value.message_type)){
                    opt.elements.msg_stack.prepend(ThreadTemplates.render().system_message(value));
                    return;
                }
                if(value.owner_id === TippinManager.common().id){
                    if(key !== 0
                        && opt.storage.messages[key-1].owner_id === value.owner_id
                        && [0,1,2].includes(opt.storage.messages[key-1].message_type)
                        && TippinManager.format().timeDiffInUnit(opt.storage.messages[key-1].created_at, value.created_at, 'minutes') < 30
                    ){
                        opt.elements.msg_stack.find("#message_"+opt.storage.messages[key-1].message_id).replaceWith(ThreadTemplates.render().my_message_grouped(opt.storage.messages[key-1]))
                    }
                    opt.elements.msg_stack.prepend(ThreadTemplates.render().my_message(value));
                    return;
                }
                if(key !== 0
                    && opt.storage.messages[key-1].owner_id === value.owner_id
                    && [0,1,2].includes(opt.storage.messages[key-1].message_type)
                    && TippinManager.format().timeDiffInUnit(opt.storage.messages[key-1].created_at, value.created_at, 'minutes') < 30
                ){
                    opt.elements.msg_stack.find("#message_"+opt.storage.messages[key-1].message_id).replaceWith(ThreadTemplates.render().message_grouped(opt.storage.messages[key-1]))
                }
                opt.elements.msg_stack.prepend(ThreadTemplates.render().message(value))
            });
            opt.storage.messages.reverse();
            methods.drawBobbleHeads();
            if(opt.elements.the_thread.prop("scrollTop") === 0){
                if(opt.storage.messages.length && opt.storage.messages[0].message_id !== data.history_id){
                    document.getElementById('message_'+data.history_id).scrollIntoView();
                    document.getElementById('msg_thread_'+opt.thread.id).scrollTop -= 40;
                    if(TippinManager.common().mobile) window.scrollTo(0, 0)
                }
                else opt.elements.the_thread.scrollTop(40);
            }
            methods.imageLoadListener(false);
            if(messages.length < 25){
                opt.thread.thread_history = false;
                opt.elements.msg_stack.prepend(ThreadTemplates.render().end_of_history());
            }
        },
        initializeRecentMessages : function(reset) {
            let onLoad = function (data) {
                if(data) opt.storage.messages = data.messages;
                opt.elements.msg_stack.html('');
                methods.manageRecentMessages();
                if(opt.storage.pending_messages.length){
                    opt.storage.pending_messages.forEach(methods.addMessage);
                    opt.storage.pending_messages = [];
                    methods.markRead();
                }
                if(!opt.storage.bobble_heads.length) LoadIn.bobbleHeads()
            };
            if(!reset && opt.storage.messages.length){
                onLoad()
            }
            else{
                opt.states.lock = true;
                TippinManager.xhr().request({
                    route : opt.API+'get/'+opt.thread.id+'/recent_messages',
                    success : onLoad,
                    fail : function(){
                        opt.states.load_in_retries++;
                        if(opt.states.load_in_retries > 4){
                            opt.elements.msg_stack.html('');
                            TippinManager.alert().Alert({
                                toast : true,
                                theme : 'warning',
                                title : 'We could not load in your messages at this time'
                            });
                            return;
                        }
                        methods.initializeRecentMessages()
                    }
                });
            }
        },
        loadHistory : function(){
            if(opt.states.lock || !opt.thread.thread_history || !opt.storage.messages.length) return;
            let history_id = opt.storage.messages[0].message_id;
            if(opt.thread.history_id === history_id){
                opt.thread.thread_history = false;
                opt.elements.msg_stack.prepend(ThreadTemplates.render().end_of_history());
                return;
            }
            opt.states.lock = true;
            opt.thread.history_id = history_id;
            opt.elements.msg_stack.prepend(ThreadTemplates.render().loading_history());
            TippinManager.xhr().request({
                route : opt.API+'get/'+opt.thread.id+'/messages/'+history_id,
                shared : {
                    history_id : history_id
                },
                success : methods.manageHistoryMessages,
                fail : function(){
                    $("#loading_history_marker").remove();
                },
                bypass : true,
                fail_alert : true
            })
        },
        isTyping : function() {
            let time = new Date();
            if(opt.socket.online_status_setting === 1 && opt.storage.active_profiles.length && opt.socket.chat && ((time.getTime() - opt.socket.send_typing) / 1000) > 1.5){
                opt.socket.send_typing = time.getTime();
                opt.socket.chat.whisper('typing', {
                    owner_id: TippinManager.common().id,
                    name: TippinManager.common().name,
                    typing: true
                });
            }
        },
        stopTyping : function(){
            if(opt.socket.online_status_setting === 1 && opt.storage.active_profiles.length && opt.socket.chat && opt.socket.send_typing > 0){
                opt.socket.send_typing = 0;
                opt.socket.chat.whisper('typing', {
                    owner_id: TippinManager.common().id,
                    name: TippinManager.common().name,
                    typing: false
                });
            }
        },
        seenMessage : function(message){
            if(opt.storage.active_profiles.length && opt.socket.chat){
                opt.socket.chat.whisper('read', {
                    owner_id : TippinManager.common().id,
                    message_id : message
                });
            }
        },
        sendOnlineStatus : function(status){
            if(!opt.storage.active_profiles.length || !opt.socket.chat) return;
            opt.socket.chat.whisper('online', {
                owner_id: TippinManager.common().id,
                name: TippinManager.common().name,
                online: (opt.socket.online_status_setting !== 0 ? status : 0)
            })
        },
        sendGroupMessageState : function(power){
            if(!opt.socket.chat) return;
            opt.socket.chat.whisper('send_message_setting', {
                send_message : power
            })
        },
        pasteImage : function(event){
            if(opt.thread.type === 3) return;
            let items = (event.clipboardData  || event.originalEvent.clipboardData).items,
            blob = null;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf("image") === 0) {
                    blob = items[i].getAsFile();
                }
            }
            if (blob !== null) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    let file = event.target.result;
                    TippinManager.alert().Modal({
                        size : 'lg',
                        theme : 'dark',
                        icon : 'image',
                        title : 'Send Screenshot?',
                        body : '<img class="img-fluid" src="'+file+'"><canvas class="NS" id="paste_canvas"></canvas>',
                        cb_btn_txt : 'Send',
                        cb_btn_icon : 'cloud-upload-alt',
                        cb_btn_theme : 'success',
                        onReady : function(){
                            let canvas = document.getElementById("paste_canvas"),
                            ctx = canvas.getContext("2d"),
                            image = new Image();
                            image.onload = function() {
                                canvas.width = image.width;
                                canvas.height = image.height;
                                ctx.drawImage(image, 0, 0);
                            };
                            image.src = file
                        },
                        callback : function(){
                            document.getElementById("paste_canvas").toBlob(function(blob){
                                methods.sendUploadFiles(blob);
                                $(".modal").modal('hide');
                                opt.elements.emoji_editor.focus()
                            }, 'image/png')
                        }
                    });
                };
                reader.readAsDataURL(blob)
            }
        },
        sendMessage : function(){
            if(!opt.thread.id || opt.thread.lockout || (!opt.thread.messaging && !opt.thread.admin)) return;
            let message_contents = (opt.elements.emoji ? opt.elements.emoji.data("emojioneArea").getText() : opt.elements.emoji_editor.val());
            if(message_contents.trim().length) {
                opt.elements.emoji ? opt.elements.emoji_editor.empty().focus() : opt.elements.emoji_editor.val('').focus();
                let pending = methods.makePendingMessage(0, message_contents);
                methods.managePendingMessage('add', pending);
                TippinManager.xhr().payload({
                    route : opt.API+'save/'+opt.thread.id,
                    data : {
                        type : 'store_message',
                        message : message_contents,
                        temp_id : pending.message_id
                    },
                    success : function(x){
                        methods.managePendingMessage('completed', pending, x.message);
                    },
                    fail : function(){
                        methods.managePendingMessage('purge', pending);
                    },
                    fail_alert : true,
                    bypass : true
                });
                methods.manageSendMessageButton()
            }
        },
        sendUploadFiles : function(file){
            let type = false,
            images = [
                'image/jpeg',
                'image/png',
                'image/bmp',
                'image/gif',
                'image/svg+xml'
            ],
            files = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/x-rar-compressed',
                'application/zip',
                'application/x-7z-compressed',
                'application/x-zip-compressed',
                'application/zip-compressed',
                'multipart/x-zip'
            ];
            if(images.includes(file.type)) type = 1;
            if(files.includes(file.type)) type = 2;
            if(!type){
                TippinManager.alert().Alert({
                    title : 'File type not supported',
                    toast : true,
                    theme : 'warning'
                });
                return;
            }
            let pending = methods.makePendingMessage(type, null);
            methods.managePendingMessage('add', pending);
            let form = new FormData();
            form.append(type === 1 ? 'image_file' : 'doc_file', file);
            form.append('type', 'store_message');
            form.append('temp_id', pending.message_id);
            TippinManager.xhr().payload({
                route : opt.API+'save/'+opt.thread.id,
                data : form,
                success : function(x){
                    methods.managePendingMessage('completed', pending, x.message)
                },
                fail : function(){
                    methods.managePendingMessage('purge', pending)
                },
                fail_alert : true,
                bypass : true
            })
        },
        makePendingMessage : function(type, body){
            return {
                avatar : TippinManager.common().slug,
                body : body ? TippinManager.format().escapeHtml(body) : null,
                created_at : null,
                extra : null,
                message_id : uuid(),
                message_type : type,
                name : TippinManager.common().name,
                owner_id : TippinManager.common().id,
                owner_type : null,
                slug : null,
                thread_id : opt.thread.id
            }
        },
        addPendingMessage : function(message){
            if(opt.storage.pending_messages.length > 1 ||
                (opt.storage.messages.length > 1
                && opt.storage.messages[(opt.storage.messages.length-1)].owner_id === TippinManager.common().id
                && [0,1,2].includes(opt.storage.messages[(opt.storage.messages.length-1)].message_type))
            ){
                opt.elements.pending_msg_stack.append(ThreadTemplates.render().pending_message_grouped(message));
            }
            else{
                opt.elements.pending_msg_stack.append(ThreadTemplates.render().pending_message(message))
            }
        },
        managePendingMessage : function(action, pending, final){
            let msg_elm = $("#pending_message_"+pending.message_id),
                storage = methods.locateStorageItem({type : 'pending_message', id : pending.message_id});
            switch (action) {
                case 'add':
                    opt.storage.pending_messages.push(pending);
                    methods.addPendingMessage(pending);
                    methods.messageStatusState(pending, false);
                    setTimeout(function () {
                        $("#pending_message_loading_"+pending.message_id).show()
                    }, 1500);
                break;
                case 'completed':
                    msg_elm.remove();
                    if(storage.found) opt.storage.pending_messages.splice(storage.index, 1);
                    methods.addMessage(final);
                break;
                case 'purge':
                    $("#pending_message_loading_"+pending.message_id).removeClass('text-primary').addClass('text-danger').show();
                    setTimeout(function () {
                        msg_elm.remove()
                    }, 5000);
                    if(storage.found) opt.storage.pending_messages.splice(storage.index, 1);
                break;
            }
        },
        locateStorageItem : function(arg){
            let collection, term,
            item = {
                found : false,
                index : 0
            };
            switch(arg.type){
                case 'message':
                    collection = opt.storage.messages;
                    term = 'message_id';
                break;
                case 'pending_message':
                    collection = opt.storage.pending_messages;
                    term = 'message_id';
                break;
                case 'thread':
                    collection = opt.storage.threads;
                    term = 'thread_id';
                break;
                case 'bobble':
                    collection = opt.storage.bobble_heads;
                    term = 'owner_id';
                break;
            }
            for(let i = 0; i < collection.length; i++) {
                if (collection[i][term] === arg.id) {
                    item.found = true;
                    item.index = i;
                    break;
                }
            }
            return item
        },
        addMessage : function(msg){
            if(msg.thread_id !== opt.thread.id) return;
            if(methods.locateStorageItem({type : 'message', id : msg.message_id}).found) return;
            if(msg.temp_id){
                let pending = methods.locateStorageItem({type : 'pending_message', id : msg.temp_id});
                if(pending.found){
                    msg.temp_id = null;
                    methods.managePendingMessage('completed', opt.storage.pending_messages[pending.index], msg);
                    return;
                }
            }
            methods.updateThread(msg, false, false, false, true);
            msg.added = true;
            opt.storage.messages.push(msg);
            methods.updateBobbleHead(msg.owner_id, msg.message_id);
            if(![0,1,2].includes(msg.message_type)){
                opt.elements.msg_stack.append(ThreadTemplates.render().system_message(msg));
            }
            else if(opt.storage.messages.length > 1
                && opt.storage.messages[(opt.storage.messages.length-2)].owner_id === msg.owner_id
                && [0,1,2].includes(opt.storage.messages[(opt.storage.messages.length-2)].message_type)
                && TippinManager.format().timeDiffInUnit(msg.created_at, opt.storage.messages[(opt.storage.messages.length-2)].created_at, 'minutes') < 30
            ){
                msg.owner_id === TippinManager.common().id ? opt.elements.msg_stack.append(ThreadTemplates.render().my_message_grouped(msg)) : opt.elements.msg_stack.append(ThreadTemplates.render().message_grouped(msg));
            }
            else{
                msg.owner_id === TippinManager.common().id ? opt.elements.msg_stack.append(ThreadTemplates.render().my_message(msg)) : opt.elements.msg_stack.append(ThreadTemplates.render().message(msg));
            }
            methods.messageStatusState(msg, true);
            methods.drawBobbleHeads();
            if(opt.timers.recent_bobble_timeout) clearTimeout(opt.timers.recent_bobble_timeout);
            opt.timers.recent_bobble_timeout = setTimeout(function(){
                methods.checkRecentBobbleHeads([88,97,98,99].includes(msg.message_type))
            }, 3000);
        },
        messageStatusState : function(message, sound){
            opt.thread.click_to_read = false;
            let didScroll = methods.threadScrollBottom(message.owner_id === TippinManager.common().id, false),
            hide = function () {
                opt.elements.new_msg_alert.hide();
                opt.thread.messages_unread = false;
                opt.elements.message_container.removeClass('msg-ctnr-unread');
            };
            methods.imageLoadListener(didScroll);
            if(didScroll && document.hasFocus() && (!opt.socket.is_away || (opt.socket.is_away && opt.socket.online_status_setting === 2))){
                hide();
                if(message.owner_id !== TippinManager.common().id || ![0,1,2].includes(message.message_type)) methods.markRead()
            }
            else if(message.owner_id === TippinManager.common().id){
                if(![0,1,2].includes(message.message_type)) methods.markRead();
                hide();
            }
            else{
                opt.thread.messages_unread = true;
                opt.elements.message_container.addClass('msg-ctnr-unread');
                if(!didScroll){
                    opt.elements.new_msg_alert.show();
                    opt.elements.new_msg_alert.html(ThreadTemplates.render().thread_new_message_alert())
                }
                else{
                    opt.thread.click_to_read = true;
                }
                if(sound) NotifyManager.sound('message')
            }
        },
        threadCallStatus : function(call){
            PageListeners.listen().disposeTooltips();
            let thread = methods.locateStorageItem({type : 'thread', id : call.thread_id});
            if(!thread.found){
                LoadIn.thread(call.thread_id);
                return;
            }
            opt.storage.threads[thread.index].call.call_id = call.call_id;
            opt.storage.threads[thread.index].call.call_type = call.call_type;
            opt.storage.threads[thread.index].call.status = call.status;
            opt.storage.threads[thread.index].call.in_call = call.in_call;
            opt.storage.threads[thread.index].call.left_call = call.left_call;
            let temp = opt.storage.threads[thread.index], call_area = $("#thread_option_call");
            opt.storage.threads.splice(thread.index, 1);
            opt.storage.threads.unshift(temp);
            methods.addThread(temp, true);
            if(opt.thread.id === call.thread_id){
                (opt.thread.can_call || call.status) ? call_area.html(ThreadTemplates.render().thread_call_state(temp)) : call_area.html('');
            }
            PageListeners.listen().tooltips()
        },
        threadOnlineStatus : function(state){
            if(opt.thread.type !== 1) return;
            let thread = methods.locateStorageItem({type : 'thread', id : opt.thread.id});
            if(thread.found){
                opt.storage.threads[thread.index].online = state;
                methods.addThread(opt.storage.threads[thread.index], false);
            }
        },
        removeThread : function(thread_id){
            let the_thread = methods.locateStorageItem({type : 'thread', id : thread_id}), elm = $("#thread_list_"+thread_id);
            if(the_thread.found){
                opt.storage.threads.splice(the_thread.index, 1)
            }
            elm.remove();
            methods.calcUnreadThreads()
        },
        updateThread : function(data, thread, subject, read, top){
            let the_thread = methods.locateStorageItem({type : 'thread', id : data.thread_id});
            if(!the_thread.found){
                if(thread){
                    opt.storage.threads.unshift(data);
                    methods.addThread(data, true);
                }
                else if("thread_id" in data){
                    LoadIn.thread(data.thread_id)
                }
                else{
                    LoadIn.threads();
                }
                return;
            }
            if(read){
                opt.storage.threads[the_thread.index].unread = false;
                opt.storage.threads[the_thread.index].unread_count = 0;
                methods.addThread(opt.storage.threads[the_thread.index], top);
                return;
            }
            if(thread){
                opt.storage.threads[the_thread.index] = data;
                methods.addThread(data, top);
                return;
            }
            if(subject){
                opt.storage.threads[the_thread.index].name = data.subject;
                methods.addThread(opt.storage.threads[the_thread.index], top);
                return;
            }
            let temp = opt.storage.threads[the_thread.index];
            opt.storage.threads.splice(the_thread.index, 1);
            temp.recent_message.body = data.body;
            temp.recent_message.message_type = data.message_type;
            temp.recent_message.name = data.name;
            temp.updated_at = data.created_at;
            if(temp.thread_type === 1 && data.thread_id !== opt.thread.id && data.owner_id !== TippinManager.common().id) temp.online = 1;
            if(temp.thread_type === 1 && data.thread_id === opt.thread.id && data.owner_id !== TippinManager.common().id){
                let bobble = methods.locateStorageItem({type : 'bobble', id : data.owner_id}), i = bobble.index;
                if(bobble.found){
                    temp.online = opt.storage.bobble_heads[i].online;
                }
            }
            if(data.owner_id === TippinManager.common().id){
                temp.unread = false;
                temp.unread_count = 0;
            }
            else if(opt.thread.id !== data.thread_id || !document.hasFocus() || opt.socket.is_away || !methods.threadScrollBottom(false, true)){
                temp.unread = true;
                temp.unread_count = temp.unread_count+1;
            }
            else{
                temp.unread = false;
                temp.unread_count = 0;
            }
            opt.storage.threads.unshift(temp);
            methods.addThread(temp, top)
        },
        addThread : function(data, top){
            methods.calcUnreadThreads();
            if(!opt.elements.thread_area.length) return;
            if(opt.states.thread_filtered){
                methods.drawThreads();
                return;
            }
            methods.checkShowThreadSearch();
            $("#no_message_warning").remove();
            let thread_elm = opt.elements.thread_area.find('#thread_list_'+data.thread_id),
            selected = data.thread_id === opt.thread.id;
            if(selected){
                opt.elements.thread_area.find('.thread_list_item').removeClass('alert-warning shadow-sm rounded');
                opt.elements.thread_area.find('.thread-group-avatar').removeClass('avatar-is-online').addClass('avatar-is-offline')
            }
            if(top || !thread_elm.length){
                thread_elm.remove();
                opt.elements.thread_area.prepend((data.thread_type === 2 ? ThreadTemplates.render().group_thread(data, selected) : ThreadTemplates.render().private_thread(data, selected)))
            }
            else{
                thread_elm.replaceWith((data.thread_type === 2 ? ThreadTemplates.render().group_thread(data, selected) : ThreadTemplates.render().private_thread(data, selected)))
            }
        },
        drawThreads : function(){
            methods.checkShowThreadSearch();
            opt.elements.thread_area.html('');
            if(!opt.states.thread_filtered){
                opt.storage.threads.forEach(function(value){
                    opt.elements.thread_area.append((value.thread_type === 2 ?
                        ThreadTemplates.render().group_thread(value, value.thread_id === opt.thread.id)
                        : ThreadTemplates.render().private_thread(value, value.thread_id === opt.thread.id))
                    )
                });
                return;
            }
            let filtered = opt.storage.threads.filter(function (thread) {
                return thread.name.toLowerCase().includes(opt.states.thread_filter_search.toLowerCase())
            });
            if(filtered.length){
                filtered.forEach(function(value){
                    opt.elements.thread_area.append((value.thread_type === 2 ?
                        ThreadTemplates.render().group_thread(value, value.thread_id === opt.thread.id)
                        : ThreadTemplates.render().private_thread(value, value.thread_id === opt.thread.id))
                    )
                });
                return;
            }
            opt.elements.thread_area.html('<h4 id="no_message_warning" class="text-center mt-4"><span class="badge badge-pill badge-secondary"><i class="fas fa-comment-slash"></i> No matches</span></h4>');
        },
        checkThreadFilters : function(e){
            if(e && e.type === 'mouseup'){
                setTimeout(methods.checkThreadFilters, 0);
                return;
            }
            let filtered = opt.states.thread_filtered, search = opt.states.thread_filter_search;
            if(opt.elements.thread_search_input.val().trim().length){
                opt.states.thread_filtered = true;
                opt.states.thread_filter_search = opt.elements.thread_search_input.val();
                if(search !== opt.states.thread_filter_search) methods.drawThreads()
            }
            else{
                opt.states.thread_filtered = false;
                opt.states.thread_filter_search = null;
                if(filtered) methods.drawThreads()
            }
        },
        checkShowThreadSearch : function(){
            if(!opt.storage.threads.length){
                opt.elements.thread_search_bar.hide();
                return;
            }
            opt.elements.thread_search_bar.show()
        },
        calcUnreadThreads : function(){
            let unread = 0;
            opt.storage.threads.forEach(function(thread){
                if(thread.unread && thread.unread_count > 0) unread++;
            });
            NotifyManager.updateMessageCount({total_unread : unread})
        }
    },
    archive = {
        Message : function(arg){
            if(!opt.thread.id) return;
            let msg = $("#message_"+arg.id);
            msg.find('.message-body').addClass('border border-warning');
            msg.find('.message_hover_opt').removeClass('NS');
            TippinManager.alert().Modal({
                size : 'sm',
                body : false,
                centered : true,
                unlock_buttons : false,
                title: 'Delete message?',
                theme: 'danger',
                cb_btn_txt: 'Delete',
                cb_btn_theme : 'danger',
                cb_btn_icon:'trash',
                icon: 'trash',
                callback : function(){
                    TippinManager.xhr().payload({
                        route : opt.API+'save/'+opt.thread.id,
                        data : {
                            type : 'remove_message',
                            thread_id : opt.thread.id,
                            message_id : arg.id
                        },
                        success : function(){
                            TippinManager.alert().Alert({
                                title : 'Message Removed',
                                toast : true,
                                theme : 'warning'
                            });
                            methods.purgeMessage(arg.id);
                            msg.remove()
                        },
                        fail_alert : true,
                        close_modal : true
                    });
                },
                onClosed : function(){
                    msg.find('.message-body').removeClass('border border-warning');
                    msg.find('.message_hover_opt').addClass('NS')
                }
            });
        },
        Thread : function(){
            if(!opt.thread.id) return;
            TippinManager.alert().Modal({
                theme : 'danger',
                icon : 'trash',
                backdrop_ctrl : false,
                centered : true,
                pre_loader : true,
                title : 'Checking delete...',
                cb_btn_txt : 'Delete',
                cb_btn_icon : 'trash',
                cb_btn_theme : 'danger',
                onReady : function(){
                    TippinManager.xhr().request({
                        route : opt.API+'get/'+opt.thread.id+'/archive_thread',
                        success : function(data){
                            TippinManager.alert().fillModal({body : ThreadTemplates.render().archive_thread_warning(data), title : ' Delete Conversation?'});
                        },
                        fail : TippinManager.alert().destroyModal,
                        bypass : true,
                        fail_alert : true
                    })
                },
                callback : function(){
                    archive.postArchiveThread()
                }
            })
        },
        postArchiveThread : function(){
            if(opt.states.lock) return;
            opt.states.lock = true;
            TippinManager.xhr().payload({
                route : opt.API+'save/'+opt.thread.id,
                shared : {
                    thread_id : opt.thread.id
                },
                data : {
                    type : 'archive_thread',
                    thread_id : opt.thread.id
                },
                success : function(data){
                    LoadIn.closeOpened();
                    methods.removeThread(data.thread_id);
                    TippinManager.alert().Alert({
                        title : data.msg,
                        theme : 'error',
                        toast : true
                    })
                },
                fail_alert : true,
                close_modal : true
            });
        }
    },
    groups = {
        viewParticipants : function(reload){
            let gather = () => {
                TippinManager.xhr().request({
                    route : opt.API+'get/'+opt.thread.id+'/participants',
                    success : function(data){
                        TippinManager.alert().fillModal({
                            body : ThreadTemplates.render().group_participants(data.participants, data.admin),
                            title : opt.thread.name+' Participants'
                        });
                        methods.loadDataTable($("#view_group_participants"))
                    },
                    fail_alert : true
                })
            };
            if(reload) return gather();
            TippinManager.alert().Modal({
                icon : 'users',
                theme : 'dark',
                title : 'Loading Participants...',
                pre_loader : true,
                overflow : true,
                unlock_buttons : false,
                h4 : false,
                size : 'lg',
                onReady : gather
            });
        },
        viewInviteGenerator : function(){
            let thread =opt.thread.id;
            TippinManager.alert().Modal({
                icon : 'link',
                theme : 'dark',
                title : 'Loading Invite...',
                pre_loader : true,
                overflow : true,
                unlock_buttons : false,
                h4 : false,
                onReady : function(){
                    TippinManager.xhr().request({
                        route : opt.API+'get/'+thread+'/group_invites',
                        success : groups.manageInviteGenPage,
                        fail_alert : true
                    })
                }
            });
        },
        manageInviteGenPage : function(data){
            let generate_click = function () {
                $("#grp_inv_generate_btn").click(groups.generateInviteLink)
            }, name = opt.thread.name;
            if(data.has_invite){
                TippinManager.alert().fillModal({
                    body : ThreadTemplates.render().thread_show_invite(data.invite),
                    title : name+' Invite Generator'
                });
                let btn_remove = $("#grp_inv_remove_btn"), btn_switch = $("#grp_inv_switch_generate_btn"), copy_btn = $("#grp_inv_copy_btn");
                btn_remove.click(function () {
                    TippinManager.button().addLoader({id : btn_remove});
                    groups.removeInviteLink(data.invite.id)
                });
                btn_switch.click(function () {
                    TippinManager.alert().fillModal({
                        body : ThreadTemplates.render().thread_generate_invite(true)
                    });
                    generate_click();
                    $("#grp_inv_back_btn").click(function () {
                        groups.manageInviteGenPage(data)
                    })
                });
                copy_btn.click(function () {
                    let input = document.getElementById('inv_generated_link');
                    input.select();
                    input.setSelectionRange(0, 99999);
                    document.execCommand("copy");
                    copy_btn.removeClass('btn-primary').addClass('btn-success').html('<i class="far fa-clipboard"></i> Copied');
                    setTimeout(function () {
                        copy_btn.removeClass('btn-success').addClass('btn-primary').html('<i class="far fa-copy"></i> Copy');
                    }, 2000)
                })
            }
            else{
                TippinManager.alert().fillModal({
                    body : ThreadTemplates.render().thread_generate_invite(false),
                    title : name+' Invite Generator'
                });
                generate_click()
            }
        },
        generateInviteLink : function(){
            let expire = parseInt($("#grp_inv_expires").val()), uses = parseInt($("#grp_inv_uses").val()),
                thread = opt.thread.id;
            TippinManager.alert().fillModal({
                loader : true,
                body : null,
                title : 'Generating...'
            });
            TippinManager.xhr().payload({
                route : opt.API+'save/'+thread,
                data : {
                    type : 'store_group_invitation',
                    expires : expire,
                    uses : uses
                },
                success : groups.manageInviteGenPage,
                fail : groups.viewInviteGenerator,
                bypass : true,
                fail_alert : true
            })
        },
        removeInviteLink : function(id){
            let thread = opt.thread.id;
            TippinManager.xhr().payload({
                route : opt.API+'save/'+thread,
                data : {
                    type : 'remove_group_invitation',
                    invite_id : id
                },
                success : function () {
                    groups.manageInviteGenPage({has_invite : false})
                },
                fail : groups.viewInviteGenerator,
                bypass : true,
                fail_alert : true
            })
        },
        addParticipants : function(){
            let thread = opt.thread.id,
                name = opt.thread.name;
            TippinManager.alert().Modal({
                icon : 'user-plus',
                theme : 'dark',
                title : 'Loading contacts...',
                pre_loader : true,
                cb_btn_txt : 'Add participants',
                cb_btn_icon : 'plus-square',
                cb_btn_theme : 'success',
                overflow : true,
                h4 : false,
                size : 'lg',
                unlock_buttons : false,
                onReady : function(){
                    TippinManager.xhr().request({
                        route : opt.API+'get/'+thread+'/add_participants',
                        success : function(data){
                            TippinManager.alert().fillModal({
                                body : ThreadTemplates.render().group_add_participants(data.friends),
                                title : 'Add contacts to '+name
                            });
                            methods.loadDataTable($("#add_group_participants"));
                        },
                        fail_alert : true
                    })
                },
                callback : function(){
                    TippinManager.xhr().payload({
                        route : opt.API+'save/'+thread,
                        data : {
                            type : 'add_group_participants',
                            recipients : opt.elements.data_table.$('input[type="checkbox"]:checked').serializeArray()
                        },
                        success : function(data){
                            TippinManager.alert().Alert({
                                title : data.subject,
                                body : 'Added '+data.names,
                                toast : true
                            })
                        },
                        fail_alert : true,
                        close_modal : true
                    });
                }
            });
        },
        viewSettings : function(){
            if(opt.states.lock) return;
            opt.states.lock = true;
            TippinManager.alert().Modal({
                icon : 'cog',
                theme : 'dark',
                title: 'Loading Settings...',
                pre_loader: true,
                h4: false,
                backdrop_ctrl : false,
                unlock_buttons : false,
                cb_btn_txt : 'Save Settings',
                cb_btn_icon : 'save',
                cb_btn_theme : 'success',
                onReady: function () {
                    TippinManager.xhr().request({
                        route : opt.API+'get/'+opt.thread.id+'/group_settings',
                        success : function(data){
                            TippinManager.alert().fillModal({
                                title : opt.thread.name+' Settings',
                                body : ThreadTemplates.render().group_settings(data)
                            });
                            PageListeners.listen().tooltips();
                            $(".m_setting_toggle").change(function(){
                                $(this).is(':checked') ? $(this).closest('tr').addClass('alert-success') : $(this).closest('tr').removeClass('alert-success')
                            })
                        },
                        fail_alert : true
                    })
                },
                callback : groups.saveSettings
            });
        },
        saveSettings : function(){
            let send_messages = $("#g_s_send_message").is(":checked");
            TippinManager.xhr().payload({
                route : opt.API+'save/'+opt.thread.id,
                data : {
                    type : 'admin_group_settings',
                    subject : $('#g_s_group_subject').val(),
                    add_participant : $("#g_s_add_participants").is(":checked"),
                    admin_call : $("#g_s_admin_call").is(":checked"),
                    send_message : send_messages
                },
                success : function(data){
                    TippinManager.alert().Alert({
                        title : 'You updated '+data.subject+'\'s Settings',
                        toast : true
                    });
                    $("#group_name_area").html(data.subject);
                    opt.thread.name = data.subject;
                    methods.updateThread({thread_id : opt.thread.id, subject : data.subject}, false, true, false, false);
                    methods.sendGroupMessageState(send_messages)
                },
                fail_alert : true,
                close_modal : true
            });
        },
        groupAvatar : function(img){
            TippinManager.alert().Modal({
                icon : 'image',
                theme : 'dark',
                centered : true,
                backdrop_ctrl : false,
                title: opt.thread.name+' Avatar',
                body : ThreadTemplates.render().group_avatar(img),
                h4: false,
                unlock_buttons : false,
                onReady: mounted.avatarListener
            });
        },
        updateGroupAvatar : function(arg){
            if(arg.action === 'upload'){
                let data = new FormData();
                data.append('avatar_image_file', $('#avatar_image_file')[0].files[0]);
                data.append('action', arg.action);
                data.append('type', 'store_avatar');
                TippinManager.button().addLoader({id : '#group_avatar_upload_btn'});
                TippinManager.xhr().payload({
                    route : opt.API+'save/'+opt.thread.id,
                    data : data,
                    success : function(data){
                        TippinManager.alert().Alert({
                            title : data.msg+', reloading group',
                            toast : true
                        });
                        setTimeout(function () {
                            LoadIn.initiate_thread({thread_id : opt.thread.id, force : true})
                        }, 2500)
                    },
                    fail_alert : true,
                    close_modal : true
                });
                return;
            }
            TippinManager.button().addLoader({id : '#avatar_default_btn'});
            TippinManager.xhr().payload({
                route : opt.API+'save/'+opt.thread.id,
                data : {
                    type : 'store_avatar',
                    action : arg.action,
                    avatar : $('#default_avatar input[type="radio"]:checked').val()
                },
                success : function(data){
                    TippinManager.alert().Alert({
                        title : data.msg+', reloading group',
                        toast : true
                    });
                    setTimeout(function () {
                        LoadIn.initiate_thread({thread_id : opt.thread.id, force : true})
                    }, 2500)
                },
                fail_alert : true,
                close_modal : true
            });
        },
        removeParticipant : function(x){
            if(opt.states.lock) return;
            opt.states.lock = true;
            TippinManager.xhr().payload({
                route : opt.API+'save/'+opt.thread.id,
                data : {
                    type : 'admin_remove_participant',
                    p_id: x
                },
                success : function(data){
                    TippinManager.alert().Alert({
                        title : data.msg,
                        toast : true,
                        theme : 'error'
                    });
                    opt.elements.data_table.row($('#row_'+x)).remove().draw(false);
                },
                fail_alert : true
            });
        },
        adminParticipant : function(arg){
            if(opt.states.lock) return;
            opt.states.lock = true;
            TippinManager.alert().fillModal({loader : true});
            TippinManager.xhr().payload({
                route : opt.API+'save/'+opt.thread.id,
                data : {
                    type : arg.type,
                    p_id : arg.id
                },
                success : function (data) {
                    TippinManager.alert().Alert({
                        title : data.msg,
                        toast : true,
                        theme : (data.admin ? 'success' : 'warning')
                    });
                    groups.viewParticipants(true);
                },
                fail_alert : true
            });
        },
        leaveGroup : function(){
            TippinManager.alert().Modal({
                icon : 'sign-out-alt',
                centered : true,
                size : 'sm',
                h4 : false,
                theme : 'danger',
                title : 'Leave Group?',
                body : '<span class="h5 font-weight-bold">Are you sure you want to leave '+opt.thread.name+'?</span>',
                cb_btn_txt : 'Leave',
                cb_btn_icon : 'sign-out-alt',
                cb_btn_theme : 'danger',
                callback : function(){
                    TippinManager.xhr().payload({
                        route : opt.API+'save/'+opt.thread.id,
                        shared : {
                            thread_id : opt.thread.id
                        },
                        data : {
                            type : 'leave_group',
                            thread_id : opt.thread.id
                        },
                        success : function(data){
                            LoadIn.closeOpened();
                            methods.removeThread(data.thread_id);
                            TippinManager.alert().Alert({
                                title : data.msg,
                                toast : true,
                                theme : 'warning'
                            })
                        },
                        fail_alert : true,
                        close_modal : true
                    })
                }
            })
        }
    },
    new_forms = {
        newGroup : function(){
            let subject = $("#subject").val();
            if(opt.states.lock || !subject.trim().length) return;
            opt.states.lock = true;
            TippinManager.button().addLoader({id : '#make_thread_btn'});
            TippinManager.xhr().payload({
                route : opt.API+'save/new_group',
                data : {
                    type : 'new_group',
                    recipients : (opt.elements.data_table ? opt.elements.data_table.$('input[type="checkbox"]:checked').serializeArray() : null),
                    subject :  subject
                },
                success : function(x){
                    LoadIn.initiate_thread({
                        thread_id : x.thread_id,
                        new : true
                    })
                },
                fail_alert : true
            })
        },
        newPrivate : function(action){
            if(opt.states.lock) return;
            let form = new FormData(), message_contents = (opt.elements.emoji ? opt.elements.emoji.data("emojioneArea").getText() : opt.elements.emoji_editor.val()),
                recipient = opt.storage.temp_data.type+'_'+opt.storage.temp_data.owner_id;
            switch (action) {
                case 0:
                    if(!message_contents.trim().length) return;
                    form.append('message', message_contents);
                    opt.elements.emoji ? opt.elements.emoji_editor.empty().focus() : opt.elements.emoji_editor.val('').focus();
                break;
                case 1:
                    form.append('image_file', $('#image_file')[0].files[0]);
                break;
                case 2:
                    form.append('doc_file', $('#doc_file')[0].files[0]);
                break;
            }
            form.append('type', 'new_private');
            form.append('recipient', recipient);
            opt.states.lock = true;
            opt.elements.msg_stack.html(ThreadTemplates.render().loader());
            TippinManager.xhr().payload({
                route : opt.API+'save/new_private',
                data : form,
                success : function(x){
                    LoadIn.initiate_thread({
                        thread_id : x.thread_id,
                        new : true
                    })
                },
                fail : LoadIn.closeOpened,
                fail_alert : true,
                bypass : true
            })
        }
    },
    Calls = {
        initCall : function(){
            if(opt.states.lock) return;
            opt.states.lock = true;
            TippinManager.button().addLoader({id : '.video_btn'});
            TippinManager.xhr().payload({
                route : opt.API+'save/'+opt.thread.id,
                data : {
                    type : 'initiate_call'
                },
                success : function(data){
                    CallManager.join(data, false);
                    TippinManager.button().removeLoader()
                },
                fail_alert : true
            })
        },
        sendKnock : function(){
            if(opt.states.lock || !NotifyManager.sockets().status) return;
            TippinManager.button().addLoader({id : '#knok_btn'});
            TippinManager.xhr().payload({
                route : opt.API+'save/'+opt.thread.id,
                data : {
                    type : 'send_knock'
                },
                success : function(data){
                    NotifyManager.sound('knok');
                    TippinManager.alert().Alert({
                        close : true,
                        title : 'Knock Knock!',
                        body : 'Knock sent to '+data.name,
                        toast : true
                    })
                },
                fail : function(data){
                    TippinManager.alert().Alert({
                        close : true,
                        title : data.data.errors.forms,
                        toast : true,
                        theme : 'warning'
                    })
                }
            })
        }
    },
    LoadIn = {
        closeOpened : function(){
            if(opt.states.lock) return;
            if(TippinManager.common().mobile) ThreadTemplates.mobile(false);
            mounted.reset(false);
            mounted.Initialize({
                type : 5
            });
            window.history.pushState({type : 5}, null, '/messenger')
        },
        threads : function(){
            TippinManager.xhr().request({
                route : opt.API+'get/threads',
                success : function(data){
                    opt.storage.threads = data.threads;
                    if(opt.elements.thread_area.length){
                        if(!opt.storage.threads.length){
                            methods.checkShowThreadSearch();
                            opt.elements.thread_area.html('<h4 id="no_message_warning" class="text-center mt-4"><span class="badge badge-pill badge-secondary"><i class="fas fa-comments"></i> No conversations</span></h4>');
                            return;
                        }
                        methods.drawThreads();
                    }
                    methods.calcUnreadThreads()
                },
                fail : function(){
                    if(opt.states.load_in_retries >= 6){
                        TippinManager.alert().Alert({
                            theme : 'error',
                            title : 'We could not load in your threads. Please try refreshing your browser page',
                            toast : true
                        });
                        return;
                    }
                    opt.states.load_in_retries++;
                    LoadIn.threads()
                }
            })
        },
        threadLogs : function(){
            if(!opt.thread.id) return;
            TippinManager.alert().Modal({
                size : 'lg',
                overflow : true,
                theme : 'dark',
                icon : 'database',
                title: 'Loading Logs...',
                pre_loader: true,
                h4: false,
                onReady: function () {
                    TippinManager.xhr().request({
                        route : opt.API+'get/'+opt.thread.id+'/thread_logs',
                        success : function(data){
                            TippinManager.alert().fillModal({
                                title : opt.thread.name+' Logs',
                                body : data.messages.length ? ThreadTemplates.render().thread_logs(data.messages) : '<h3 class="text-center mt-2"><span class="badge badge-pill badge-secondary"><i class="fas fa-database"></i> No logs</span></h3>'
                            });
                            let elm = $("#body_modal");
                            setTimeout(function () {
                                elm.scrollTop(elm.prop("scrollHeight"))
                            }, 500)
                        }
                    })
                }
            })
        },
        thread : function(thread_id){
            TippinManager.xhr().request({
                route : opt.API+'get/'+thread_id+'/load_thread',
                success : function(data){
                    let thread = methods.locateStorageItem({type : 'thread', id : thread_id});
                    if(!thread.found) opt.storage.threads.unshift(data);
                    methods.addThread(data, true)
                },
                fail_alert : true
            })
        },
        bobbleHeads : function(){
            if(!opt.thread.id) return;
            TippinManager.xhr().request({
                route : opt.API+'get/'+opt.thread.id+'/bobble_heads',
                success : function(data){
                    opt.storage.bobble_heads = data.bobble_heads;
                    $(".bobble-head-item").remove();
                    if(opt.storage.active_profiles.length){
                        opt.storage.active_profiles.forEach(function(value){
                            methods.updateBobbleHead(value.owner_id, null)
                        })
                    }
                    if(opt.thread.type === 1 && opt.storage.bobble_heads.length){
                        methods.threadOnlineStatus(opt.storage.bobble_heads[0].online);
                    }
                    methods.drawBobbleHeads()
                },
                fail : null
            })
        },
        search : function(noHistory){
            if(!opt.INIT) return;
            if(TippinManager.common().mobile) ThreadTemplates.mobile(true);
            opt.elements.message_container.html(ThreadTemplates.render().search_base());
            mounted.reset(false);
            mounted.Initialize({
                type : 7,
            });
            if(!noHistory) window.history.pushState({type : 7}, null, '/messenger?search');
        },
        contacts : function(noHistory){
            if(!opt.INIT) return;
            if(TippinManager.common().mobile) ThreadTemplates.mobile(true);
            opt.elements.message_container.html(ThreadTemplates.render().contacts_base());
            mounted.reset(false);
            opt.thread.type = 6;
            TippinManager.xhr().request({
                route : '/demo-api/friends',
                success : function(data){
                    $("#messenger_contacts_ctnr").html(ThreadTemplates.render().contacts(data.friends));
                    if(!noHistory) window.history.pushState({type : 6}, null, '/messenger?contacts');
                    methods.loadDataTable( $("#contact_list_table"), true)
                },
                fail : LoadIn.closeOpened,
                fail_alert : true,
                bypass : true
            })
        },
        createPrivate : function(arg, noHistory){
            opt.elements.message_container.html(ThreadTemplates.render().loading_thread_base());
            mounted.reset(false);
            if(TippinManager.common().mobile) ThreadTemplates.mobile(true);
            $(".modal").modal('hide');
            TippinManager.xhr().request({
                route : opt.API+'create/'+arg.slug+'/'+arg.type,
                success : function(data){
                    if(data.exist){
                        LoadIn.initiate_thread({thread_id : data.thread_id});
                        return;
                    }
                    opt.elements.message_container.html(ThreadTemplates.render().render_new_private(data.party));
                    if(!noHistory) window.history.pushState({type : 3, create_slug : arg.slug, create_type : arg.type}, null, '/messenger/create/'+arg.slug+'/'+arg.type);
                    mounted.Initialize({
                        type : 3,
                        thread_id : 'new',
                        t_name : data.party.name,
                        temp_data : data.party
                    })
                },
                fail : LoadIn.closeOpened,
                fail_alert : true,
                bypass : true
            })
        },
        createGroup : function(noHistory){
            if(opt.states.lock) return;
            mounted.reset(false);
            opt.elements.message_container.html(ThreadTemplates.render().new_group_base());
            if(!noHistory) window.history.pushState({type : 4}, null, '/messenger?newGroup');
            if(TippinManager.common().mobile) ThreadTemplates.mobile(true);
            mounted.Initialize({
                type : 4
            });
            TippinManager.xhr().request({
                route : '/demo-api/friends',
                success : function(data){
                    if(opt.thread.type === 4){
                        $("#messages_container_new_group").html(ThreadTemplates.render().new_group_friends(data.friends));
                        methods.loadDataTable($("#add_group_participants"), true)
                    }
                },
                fail_alert : true
            })
        },
        initiate_thread : function(arg, noHistory){
            if(opt.states.lock || (arg.thread_id === opt.thread.id && !("force" in arg))) return;
            if(TippinManager.common().mobile) ThreadTemplates.mobile(true);
            opt.elements.message_container.html(ThreadTemplates.render().loading_thread_base());
            mounted.reset(true);
            opt.thread.initializing = true;
            opt.thread._id = arg.thread_id;
            TippinManager.xhr().request({
                route : opt.API+'get/'+arg.thread_id+'/initiate_thread',
                success : function(data){
                    if("thread" in data){
                        switch (data.thread.thread_type) {
                            case 1:
                                methods.initiatePrivate(arg, data, noHistory);
                            break;
                            case 2:
                                methods.initiateGroup(arg, data, noHistory);
                            break;
                            default : LoadIn.closeOpened();
                        }
                    }
                    else{
                        LoadIn.closeOpened();
                    }
                },
                fail : LoadIn.closeOpened,
                bypass : true,
                fail_alert : true
            })
        }
    };
    return {
        init : mounted.Initialize,
        Import : function(){
            return Imports
        },
        newForms : function(){
            return new_forms
        },
        calls : function(){
            return Calls
        },
        send : methods.sendMessage,
        archive : function(){
            return archive
        },
        group : function() {
            return groups
        },
        load : function(){
            return LoadIn
        },
        switchToggle : mounted.switchToggleListener,
        lock : function(arg){
            if(typeof arg === 'boolean') opt.states.lock = arg
        },
        state : function(){
            return {
                thread_id : opt.thread.id,
                thread_lockout : opt.thread.lockout,
                type : opt.thread.type,
                thread_admin : opt.thread.admin,
                t_name : opt.thread.name,
                online_status : opt.socket.online_status_setting,
                socketStatusCheck : Health.checkConnection,
                reConnected : Health.reConnected,
                online : function(state){
                    methods.statusOnline(state, true)
                },
                statusSetting : methods.updateOnlineStatusSetting
            };
        },
        toggle : mounted.toggleApp
    };
}());
