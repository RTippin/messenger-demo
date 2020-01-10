/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');
window.io = require('socket.io-client');

window.NotifyManager = (function () {
    var opt = {
        sounds : {
            notify_sound_file : new Audio('/sounds/notify_tone.mp3'),
            message_sound_file : new Audio('/sounds/message_tone.mp3'),
            call_sound_file : new Audio('/sounds/call_tone.mp3'),
            knok_sound_file : new Audio('/sounds/knok.mp3')
        },
        elements : {
            notify_count_area : $("#nav_notify_count"),
            thread_count_area : $("#nav_thread_count"),
            pending_friends_count_area : $("#nav_friends_count"),
            mobile_nav_count_area : $("#nav_mobile_total_count"),
            notify_area : $("#notification_container"),
            active_call_link : $("#active_calls_nav"),
            pending_friends_link : $("#pending_friends_nav"),
            active_calls_ctnr : $("#active_calls_ctnr"),
            pending_friends_ctnr : $("#pending_friends_ctnr")
        },
        settings : {
            notifications : true,
            total_notify_count : 0,
            message_popups : true,
            notify_sound : true,
            message_sound : true,
            call_ringtone_sound : true,
            sound_playing : false,
            is_away : false
        },
        storage : {
            unread_notify : 0,
            unread_thread : 0,
            pending_friends_count : 0,
            active_calls : null,
            pending_friends : null,
            original_title : null,
            current_title : null,
            heartbeat_interval : null,
            toggle_title_interval : null
        },
        socket : {
            Echo : null,
            private_channel : null,
            socket_status : false,
            forced_disconnect : false
        }
    },
    Initialize = {
        Init : function(arg){
            opt.settings.message_popups = arg.message_popups;
            opt.settings.notify_sound = arg.notify_sound;
            opt.settings.message_sound = arg.message_sound;
            opt.settings.call_ringtone_sound = arg.call_ringtone_sound;
            NetworksManager.init();
            broadcaster.Echo(false);
            broadcaster.heartBeat(true, false, false);
            broadcaster.heartBeat(false, true, true);
            opt.storage.original_title = document.head.querySelector('meta[name="title"]').content;
            opt.storage.current_title = opt.storage.original_title;
            $('.notify-drop').click(function(e){
                e.stopPropagation();
            });
            opt.sounds.message_sound_file.volume = 0.2;
            InactivityManager.setup({
                type : 1,
                inactive : function(){
                    broadcaster.Disconnect();
                    broadcaster.heartBeat(false, false, false);
                    if(TippinManager.common().modules.includes('ThreadManager'))ThreadManager.state().socketStatusCheck()

                },
                activate : function(){
                    if(CallManager.state().initialized){
                        window.location.reload();
                        return;
                    }
                    TippinManager.xhr().request({
                        route : '/auth/heartbeat',
                        success : function(data){
                            methods.manageHeartbeatData(data);
                            opt.settings.is_away = false;
                            broadcaster.heartBeat(true, false, false);
                            broadcaster.Echo((TippinManager.common().modules.includes('ThreadManager') ?
                                {
                                    onConnect : function(){
                                        ThreadManager.state().reConnected(true)
                                    }
                                }
                                : false
                            ))
                        }
                    });
                }
            });
            InactivityManager.setup({
                type : 2,
                inactive : function(){
                    if(TippinManager.common().modules.includes('ThreadManager')) ThreadManager.state().online(2);
                    opt.settings.is_away = true;
                    if(!opt.socket.forced_disconnect) broadcaster.heartBeat(false, true, false)
                },
                activate : function(){
                    if(TippinManager.common().modules.includes('ThreadManager')) ThreadManager.state().online(1);
                    opt.settings.is_away = false;
                    if(!opt.socket.forced_disconnect) broadcaster.heartBeat(false, true, false)
                }
            })
        }
    },
    broadcaster = {
        Echo : function(onConnect){
            opt.socket.forced_disconnect = false;
            opt.socket.Echo = new Echo({
                broadcaster: 'socket.io',
                host: process.env.MIX_SOCKET_APP_HOST + ":" + process.env.MIX_SOCKET_APP_PORT
            });
            opt.socket.Echo.connector.socket.on('connect', function(){
                opt.socket.socket_status = true;
                broadcaster.Broadcast(TippinManager.common().id);
                if(onConnect) onConnect.onConnect()
            });
            opt.socket.Echo.connector.socket.on('reconnect', function(){
                broadcaster.heartBeat(false, true, true);
                if(TippinManager.common().modules.includes('ThreadManager')) ThreadManager.state().reConnected();
                if(CallManager.state().initialized) CallManager.channel().reconnected()
            });
            opt.socket.Echo.connector.socket.on('disconnect', function(){
                opt.socket.socket_status = false;
                if(TippinManager.common().modules.includes('ThreadManager')) ThreadManager.state().socketStatusCheck();
                if(CallManager.state().initialized) CallManager.channel().disconnected()
            })
        },
        Disconnect : function(){
            if(opt.socket.Echo !== null) opt.socket.Echo.disconnect();
            opt.socket.forced_disconnect = true;
            opt.socket.socket_status = false;
        },
        Broadcast : function(id){
            if(!opt.socket.Echo) return;
            if(typeof opt.socket.Echo.connector.channels['private-'+TippinManager.common().model+'_notify_'+id] !== 'undefined'){
                opt.socket.private_channel = opt.socket.Echo.connector.channels['private-'+TippinManager.common().model+'_notify_'+id];
                return;
            }
            opt.socket.private_channel = opt.socket.Echo.private(TippinManager.common().model+'_notify_'+id);
            opt.socket.private_channel.listen('.message_received', methods.incomingMessage)
            .listen('.add_group', methods.addedToGroup)
            .listen('.message_purged', methods.messagePurged)
            .listen('.new_call', methods.incomingCall)
            .listen('.call_ended', methods.callEnded)
            .listen('.kicked', methods.incomingKicked)
            .listen('.knok', methods.incomingKnok)
            .notification(methods.incomingNotify)
        },
        heartBeat : function(state, check, gather){
            let request = function (){
                TippinManager.heartbeat().gather(methods.manageHeartbeatData, function () {
                    window.location.reload()
                })
            },
            payload = function(){
                TippinManager.heartbeat().update((opt.settings.is_away ? 2 : 1), methods.manageHeartbeatData, request)
            };
            if(check){
                gather ? request() : payload();
                return;
            }
            if(!state){
                clearInterval(opt.storage.heartbeat_interval);
                opt.storage.heartbeat_interval = null;
                return;
            }
            opt.storage.heartbeat_interval = setInterval(function(){
                payload()
            }, 60000)
        }
    },
    methods = {
        incomingCall : function(call){
            if(CallManager.state().initialized || !opt.settings.notifications) return;
            broadcaster.heartBeat(false, true, true);
            methods.togglePageTitle(call.sender_name+' is calling');
            CallManager.newCall(call)
        },
        callEnded : function(call){
            if(!opt.settings.notifications) return;
            CallManager.callEnded(call);
            broadcaster.heartBeat(false, true, true);
        },
        incomingNotify : function(data){
            if(CallManager.state().initialized || !opt.settings.notifications) return;
            TippinManager.alert().Alert({
                title : 'New Notification',
                body : data.message,
                toast : true,
                theme : 'info'
            });
            broadcaster.heartBeat(false, true, true);
            methods.togglePageTitle('New Notification');
            methods.playAlertSound('notify');
        },
        incomingMessage : function(data){
            if(!opt.settings.notifications) return;
            methods.togglePageTitle(data.name+' says...');
            if(TippinManager.common().modules.includes('ThreadManager')){
                ThreadManager.Import().newMessage(data);
                return;
            }
            if(CallManager.state().initialized) return;
            broadcaster.heartBeat(false, true, true);
            methods.playAlertSound('message');
            if(![0,1,2].includes(data.message_type) || !opt.settings.message_popups) return;
            let body = null;
            switch(data.message_type){
                case 0:
                    body = data.body.length > 45 ? emojione.toImage(data.body.substring(0, 42) + "...") : emojione.toImage(data.body);
                break;
                case 1:
                    body = "Sent an image";
                break;
                case 2:
                    body = "Sent a file";
                break;
            }
            TippinManager.alert().Alert({
                title : (data.thread_type === 2 ? data.thread_subject : data.name),
                body : body,
                toast : true,
                theme : 'info',
                toast_options : {
                    onclick : function(){
                        window.location.href = '/messenger/'+data.thread_id
                    },
                    timeOut : 5000
                }
            })
        },
        incomingKnok : function(data){
            if(!opt.settings.notifications) return;
            if(CallManager.state().initialized){
                if(CallManager.state().thread_id === data.thread_id){
                    methods.playAlertSound('knok');
                    methods.togglePageTitle(data.name+' is knocking...');
                }
                return;
            }
            if(TippinManager.common().modules.includes('ThreadManager') && ThreadManager.state().thread_id === data.thread_id){
                methods.playAlertSound('knok');
                methods.togglePageTitle(data.name+' is knocking...');
                return;
            }
            TippinManager.alert().Modal({
                wait_for_others : true,
                theme : 'dark',
                icon : 'hand-rock',
                size : 'sm',
                centered : true,
                title : 'Knock Knock',
                body : '<div class="col-12 mb-3"><div class="text-center text-dark"><div id="knok_animate"><i  class="fas fa-hand-rock fa-7x"></i></div></div></div>' +
                    '<div class="col-12 text-center"> <img height="25" width="25" class="mr-2 rounded-circle" src="'+data.avatar+'" /><span class="h6 font-weight-bold">'+data.name+'</span></div>',
                onReady : function(){
                    methods.playAlertSound('knok');
                    methods.togglePageTitle(data.name+' is knocking...');
                    PageListeners.listen().animateKnok(true)
                },
                cb_btn_txt : 'View',
                cb_btn_icon : 'comment-dots',
                cb_btn_theme : 'success',
                onClose : function(){
                    PageListeners.listen().animateKnok(false)
                },
                callback : function(){
                    if(TippinManager.common().modules.includes('ThreadManager')){
                        ThreadManager.load().initiate_thread({thread_id : data.thread_id});
                        return;
                    }
                    window.location.href = '/messenger/'+data.thread_id
                },
                cb_close : true,
                timer : 15000
            })
        },
        addedToGroup : function(data){
            if(!opt.settings.notifications) return;
            if(TippinManager.common().modules.includes('ThreadManager')){
                ThreadManager.Import().addedToGroup(data.thread_id);
                return;
            }
            broadcaster.heartBeat(false, true, true);
            methods.playAlertSound('message');
            TippinManager.alert().Alert({
                title : data.subject,
                body : data.name+' added you to the group',
                toast : true,
                theme : 'info',
                toast_options : {
                    onclick : function(){
                        window.location.href = '/messenger/'+data.thread_id
                    }
                }
            })
        },
        messagePurged : function(data){
            if(!opt.settings.notifications) return;
            if(TippinManager.common().modules.includes('ThreadManager')){
                ThreadManager.Import().purgeMessage(data);
            }
        },
        incomingKicked : function(data){
            if(!opt.settings.notifications) return;
            if(TippinManager.common().modules.includes('ThreadManager')){
                ThreadManager.Import().removedFromGroup(data.thread_id);
            }
        },
        manageNotifyData : function(data){
            opt.storage.unread_notify = 0;
            if(data.state){
                opt.elements.notify_area.html(data.html);
                opt.settings.total_notify_count = data.count;
            }
            else{
                if(data.count === 0) opt.elements.notify_area.html('<div class="col-12 text-center h5 mt-2"><span class="badge badge-pill badge-secondary"><i class="fas fa-bell"></i> No Notifications</span></div>');
                opt.settings.total_notify_count = data.count;
            }
            methods.updatePageStates();
            setTimeout(function(){
                $('#notification_container > a.bg-warning').removeClass('bg-warning');
            }, 3500)
        },
        manageMessageCounts : function(data){
            opt.storage.unread_thread = data.total_unread;
            methods.updatePageStates()
        },
        manageHeartbeatData : function(data){
            if(data.auth && data.model === TippinManager.common().model){
                TippinManager.token(data.token);
                if("states" in data){
                    opt.storage.unread_notify = data.states.unread_notify_count;
                    opt.storage.unread_thread = data.states.unread_threads_count;
                    opt.storage.active_calls = (data.states.active_calls && data.states.active_calls.length ? data.states.active_calls : null);
                    opt.storage.pending_friends_count = (data.states.pending_friends && data.states.pending_friends.length ? data.states.pending_friends.length : 0);
                    opt.storage.pending_friends = (data.states.pending_friends && data.states.pending_friends.length ? data.states.pending_friends : null);
                }
                methods.updatePageStates();
                return;
            }
            window.location.reload()
        },
        updatePageStates : function(){
            if(!CallManager.state().initialized){
                if(opt.storage.active_calls && opt.storage.active_calls.length){
                    opt.elements.active_call_link.show()
                }
                else{
                    opt.elements.active_call_link.hide()
                }
            }
            methods.updateTitle();
            methods.updateActiveCalls();
            methods.updatePendingFriends();
            opt.storage.unread_notify > 0 ? opt.elements.notify_count_area.html(opt.storage.unread_notify) : opt.elements.notify_count_area.html('');
            opt.storage.unread_thread > 0 ? opt.elements.thread_count_area.html(opt.storage.unread_thread) : opt.elements.thread_count_area.html('');
            opt.storage.pending_friends_count > 0 ? opt.elements.pending_friends_count_area.html(opt.storage.pending_friends_count) : opt.elements.pending_friends_count_area.html('');
            if(opt.storage.unread_notify > 0 || opt.storage.unread_thread > 0 || opt.storage.pending_friends_count > 0){
                opt.elements.mobile_nav_count_area.html(opt.storage.unread_thread+opt.storage.unread_notify+opt.storage.pending_friends_count);
                return;
            }
            opt.elements.mobile_nav_count_area.html('')
        },
        updateActiveCalls : function(){
            if(CallManager.state().initialized) return;
            if(!opt.storage.active_calls || !opt.storage.active_calls.length){
                opt.elements.active_calls_ctnr.html('');
                return;
            }
            opt.elements.active_calls_ctnr.html('');
            opt.storage.active_calls.forEach(function(call){
                opt.elements.active_calls_ctnr.append(templates.active_call(call))
            });
        },
        updatePendingFriends : function(){
            if(CallManager.state().initialized) return;
            if(!opt.storage.pending_friends || !opt.storage.pending_friends.length){
                opt.elements.pending_friends_ctnr.html('<div class="col-12 text-center h5 mt-2"><span class="badge badge-pill badge-secondary"><i class="fas fa-user-friends"></i> No Friend Request</span></div>');
                return;
            }
            opt.elements.pending_friends_ctnr.html('');
            opt.storage.pending_friends.forEach(function(friend){
                opt.elements.pending_friends_ctnr.append(templates.pending_friend(friend))
            })
        },
        updateTitle : function(){
            let total = opt.storage.unread_notify+opt.storage.unread_thread+opt.storage.pending_friends_count;
            if(opt.storage.active_calls && opt.storage.active_calls.length && !CallManager.state().initialized) total = total+opt.storage.active_calls.length;
            if(total > 0){
                let the_title = '('+total+') '+opt.storage.original_title;
                opt.storage.current_title = the_title;
                document.title = the_title;
                return;
            }
            document.title = opt.storage.original_title;
            opt.storage.current_title = opt.storage.original_title;
        },
        togglePageTitle : function(msg){
            methods.pageTitle(false);
            if(!document.hasFocus()){
                methods.pageTitle(true, msg);
                $(document).one("click", function(){
                    methods.pageTitle(false);
                })
            }
        },
        pageTitle : function(power, msg){
            if(power){
                opt.storage.toggle_title_interval = setInterval(function () {
                    document.title = (document.title.trim() === opt.storage.current_title.trim() ? msg : opt.storage.current_title);
                }, 3000);
                return;
            }
            if(opt.storage.toggle_title_interval) clearInterval(opt.storage.toggle_title_interval);
            opt.storage.toggle_title_interval = null;
            methods.updateTitle()
        },
        pullNotifications : function(){
            TippinManager.xhr().payload({
                route : '/notifications/gather',
                data : {
                    notify_count : opt.settings.total_notify_count
                },
                success : function(data){
                    methods.manageNotifyData(data)
                }
            })
        },
        deleteNotifications : function(){
            TippinManager.button().addLoader({id : '#del_all_notify_link'});
            TippinManager.xhr().payload({
                route : '/notifications/delete',
                data : {
                    notify_count : opt.settings.total_notify_count
                },
                success : function(data){
                    TippinManager.button().removeLoader();
                    methods.manageNotifyData(data)
                },
                fail_alert : true
            })
        },
        settingsToggle : function(arg){
            if("message_popups" in arg) opt.settings.message_popups = arg.message_popups;
            if("message_sound" in arg) opt.settings.message_sound = arg.message_sound;
            if("notify_sound" in arg) opt.settings.notify_sound = arg.notify_sound;
            if("call_ringtone_sound" in arg) opt.settings.call_ringtone_sound = arg.call_ringtone_sound;
            if("notifications" in arg) opt.settings.notifications = arg.notifications;
        },
        playAlertSound : function(type){
            let soundOff = function () {
                opt.settings.sound_playing = false;
            };
            switch(type){
                case 'message':
                    if(!opt.settings.message_sound || opt.settings.sound_playing) return;
                    opt.settings.sound_playing = true;
                    opt.sounds.message_sound_file.play().then(soundOff).catch(soundOff);
                break;
                case 'notify':
                    if(!opt.settings.notify_sound || opt.settings.sound_playing) return;
                    opt.settings.sound_playing = true;
                    opt.sounds.notify_sound_file.play().then(soundOff).catch(soundOff);
                break;
                case 'call':
                    if(!opt.settings.call_ringtone_sound || opt.settings.sound_playing) return;
                    opt.settings.sound_playing = true;
                    opt.sounds.call_sound_file.play().then(soundOff).catch(soundOff);
                break;
                case 'knok':
                    if(opt.settings.sound_playing) return;
                    opt.settings.sound_playing = true;
                    opt.sounds.knok_sound_file.play().then(soundOff).catch(soundOff);
                break;
            }
        },
        callAction : function (id) {
            if(!opt.storage.active_calls || !opt.storage.active_calls.length) return;
            for(let i = 0; i < opt.storage.active_calls.length; i++) {
                if (opt.storage.active_calls[i].call_id === id) {
                    CallManager.join(opt.storage.active_calls[i], !opt.storage.active_calls[i].in_call);
                    break;
                }
            }
        },
        pendingFriendAction : function (id, action) {
            for(let i = 0; i < opt.storage.pending_friends.length; i++) {
                if(opt.storage.pending_friends[i].id === id){
                    $("#friend_actions_"+id).remove();
                    NetworksManager.action({
                        action : action,
                        slug : opt.storage.pending_friends[i].slug,
                        type : opt.storage.pending_friends[i].type
                    });
                    break;
                }
            }
        }
    },
    templates = {
        active_call : function (call) {
            let bg = 'warning', color = 'dark', type = 'Call',
                msg = 'Click to join', action = "NotifyManager.calls('"+call.call_id+"');",
                icon = '<i class="fas fa-video"></i>';
            if(call.in_call){
                bg = 'danger';
                color = 'light';
                msg = 'Currently in '+type;
            }
            else if(call.left_call){
                bg = 'secondary';
                color = 'light';
                msg = 'Click to rejoin';
            }
            return '<a onclick="'+action+' return false;" href="#" class="list-group-item list-group-item-action p-2 text-'+color+' bg-'+bg+'">\n' +
                '    <div class="media">\n' +
                '        <div class="media-left media-top">\n' +
                '            <img class="rounded media-object" height="50" width="50" src="'+call.avatar+'">\n' +
                '        </div>\n' +
                '        <div class="media-body">\n' +
                '            <h6 class="ml-2 mb-1 font-weight-bold">'+icon+' '+call.name+' - '+type+'</h6>\n' +
                '            <div class="mt-2"><span class="float-right"><span class="badge badge-pill badge-light">'+icon+' '+msg+'</span></span></div>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '</a>'
        },
        pending_friend : function (friend) {
            return '<a onclick="return false;" href="#" class="list-group-item list-group-item-action p-2 text-dark bg-light">\n' +
                '    <div class="media">\n' +
                '        <div class="media-left media-top" onclick="window.location.href=\'/'+friend.type+'/profile/'+friend.slug+'\'">\n' +
                '            <img class="rounded media-object" height="50" width="50" src="'+friend.avatar+'">\n' +
                '        </div>\n' +
                '        <div class="media-body">\n' +
                '        <span class="mt-n1 float-right small">'+TippinManager.format().makeTimeAgo(friend.utc_created_at)+' <i class="far fa-clock"></i></span>'+
                '            <h6 onclick="window.location.href=\'/'+friend.type+'/profile/'+friend.slug+'\'" class="ml-2 mb-1 font-weight-bold">'+friend.name+'</h6>\n' +
                '            <div id="friend_actions_'+friend.id+'" class="mt-2 col-12 px-0">' +
                '               <span class="float-right">' +
                '                   <button title="Accept friend request" onclick="NotifyManager.pendingFriends(\''+friend.id+'\', \'accept\')" class="btn btn-sm btn-success pt-1 pb-0 px-1"><i class="h5 far fa-check-circle"></i></button>' +
                '                   <button title="Deny friend request" onclick="NotifyManager.pendingFriends(\''+friend.id+'\', \'deny\')" class="btn btn-sm btn-danger mx-1 pt-1 pb-0 px-1"><i class="h5 fas fa-ban"></i></button>' +
                '                   <button title="Message" onclick="window.location.href=\'/'+friend.type+'/profile/'+friend.slug+'/message\'" class="btn btn-sm btn-primary pt-1 pb-0 px-1"><i class="h5 fas fa-comments"></i></button>'+
                '               </span>' +
                '            </div>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '</a>'
        }
    };
    return {
        init : Initialize.Init,
        pullNotify : methods.pullNotifications,
        deleteNotify : methods.deleteNotifications,
        newMessage : methods.incomingMessage,
        updateMessageCount : methods.manageMessageCounts,
        sound : methods.playAlertSound,
        settings : methods.settingsToggle,
        calls : methods.callAction,
        pendingFriends : methods.pendingFriendAction,
        heartbeat : function(){
            broadcaster.heartBeat(false, true, true);
        },
        counts : function(){
            return {
                notify : opt.storage.unread_notify,
                threads : opt.storage.unread_thread
            }
        },
        sockets : function(){
            return {
                forced_disconnect : opt.socket.forced_disconnect,
                status : opt.socket.socket_status,
                Echo : opt.socket.Echo,
                away : opt.settings.is_away,
                disconnect : broadcaster.Disconnect
            }
        }
    };
}());
