window.CallManager = (function () {
    var opt = {
        initialized : false,
        processing : false,
        call : false,
        call_id : null,
        call_mode : null,
        created_at : null,
        call_type : null,
        call_owner : null,
        thread_id : null,
        thread_type : null,
        thread_name : null,
        call_admin : null,
        thread_admin : null,
        channel : null,
        channel_status : false,
        active_profiles : [],
        heartbeat_interval : null,
        heartbeat_retries : 0
    },
    mounted = {
        Initialize : function(arg){
            if(opt.initialized) return;
            opt.call = true;
            opt.call_id = arg.call_id;
            opt.call_type = arg.call_type;
            opt.call_mode = arg.call_mode;
            opt.call_owner = arg.call_owner;
            opt.thread_id = arg.thread_id;
            opt.thread_type = arg.thread_type;
            opt.thread_name = arg.thread_name;
            opt.call_admin = arg.call_admin;
            opt.thread_admin = arg.thread_admin;
            opt.created_at = arg.created_at;
            if(!TippinManager.common().modules.includes('NotifyManager') || !NotifyManager.sockets().status){
                setTimeout(function () {
                    mounted.Initialize(arg)
                }, 100);
                return;
            }
            opt.initialized = true;
            if(opt.call_type === 1){
                window.addEventListener("beforeunload", methods.windowClosed, false);
                window.addEventListener("keydown", methods.checkForRefresh, false);
            }
            Sockets.heartbeat(false);
            Sockets.setup();
            if(opt.call_type === 1) Sockets.setupRTC()
        }
    },
    Sockets = {
        heartbeat : function(check){
            let beat = function(){
                if(!NotifyManager.sockets().forced_disconnect){
                    TippinManager.xhr().request({
                        route : window.location.href+"/heartbeat",
                        success : function(){
                            opt.heartbeat_retries = 0
                        },
                        fail : Sockets.heartbeatFailed
                    })
                }
            };
            beat();
            if(check) return;
            opt.heartbeat_interval = setInterval(beat, 15000)
        },
        heartbeatFailed : function(){
            opt.heartbeat_retries++;
            if(opt.heartbeat_retries < 4) Sockets.heartbeat(true);
            if(opt.heartbeat_retries >= 4){
                clearInterval(opt.heartbeat_interval);
                if(opt.channel) opt.channel.unsubscribe();
                if(TippinManager.common().modules.includes('WebRTCManager')) WebRTCManager.shutdown();
                TippinManager.alert().Modal({
                    allow_close : false,
                    size : 'sm',
                    theme : 'danger',
                    centered : true,
                    icon : 'times',
                    title : 'Call error',
                    body : '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"></div></div>\n' +
                        '<div class="mt-3">Call may have ended...one moment please</div>'
                });
                setTimeout(function () {
                    window.close()
                }, 3000)
            }
        },
        setup : function(){
            opt.channel = NotifyManager.sockets().Echo.join('call_'+opt.thread_id+'_'+opt.call_id);
            opt.channel.here(function(users){
                opt.active_profiles = [];
                opt.channel_status = true;
                $.each(users, function() {
                    if(this.owner_id !== TippinManager.common().id){
                        opt.active_profiles.push({
                            owner_id : this.owner_id,
                            avatar : this.avatar,
                            name : this.name
                        })
                    }
                })
            })
            .joining(function(user) {
                opt.active_profiles.push({
                    owner_id : user.owner_id,
                    avatar : user.avatar,
                    name : user.name
                });
                Sockets.pushJoin(user)
            })
            .leaving(function(user) {
                for(let i = 0; i < opt.active_profiles.length; i++) {
                    if (opt.active_profiles[i].owner_id === user.owner_id){
                        opt.active_profiles.splice(i, 1);
                        break;
                    }
                }
                Sockets.pushLeave(user)
            })
        },
        setupRTC : function(){
            if(!opt.channel_status || !TippinManager.common().modules.includes('WebRTCManager')){
                setTimeout(function () {
                    Sockets.setupRTC()
                }, 0);
                return;
            }
            WebRTCManager.setup()
        },
        pushJoin : function (user) {
            if(!TippinManager.common().modules.includes('WebRTCManager')){
                setTimeout(function () {
                    Sockets.pushJoin(user)
                }, 0);
                return;
            }
            WebRTCManager.peerJoin(user)
        },
        pushLeave : function (user) {
            if(!TippinManager.common().modules.includes('WebRTCManager')){
                setTimeout(function () {
                    Sockets.pushLeave(user)
                }, 0);
                return;
            }
            WebRTCManager.peerLeave(user)
        },
        disconnected : function () {
            if(TippinManager.common().modules.includes('WebRTCManager')) WebRTCManager.socket().onDisconnect()
        },
        reconnected : function () {
            if(TippinManager.common().modules.includes('WebRTCManager')) WebRTCManager.socket().onReconnect()
        }
    },
    templates = {
        call_alert : function(data){
            return '<div class="col-12 text-center mb-1"><h4 class="font-weight-bold">'+(data.thread_type === 2 ? data.thread_name : data.sender_name)+'</h4>' +
                    '<img class="img-fluid rounded" src="'+data.avatar+'" /></div>'
        }
    },
    methods = {
        windowClosed : function(){
            if(window.opener){
                window.opener.CallManager.leave(true, {call_type : 1, call_id : opt.call_id, thread_id : opt.thread_id})
            }
        },
        checkForRefresh : function(e){
            if(e.key === 'F5' || (e.ctrlKey && e.key === 'r')){
                window.removeEventListener("beforeunload", methods.windowClosed, false)
            }
        },
        updateMessenger : function(call){
            if(TippinManager.common().modules.includes('ThreadManager')){
                ThreadManager.Import().callStatus({
                    thread_id : call.thread_id,
                    status : call.status,
                    call_id : call.call_id,
                    call_type : call.call_type,
                    in_call : call.in_call,
                    left_call : call.left_call
                })
            }
        },
        incomingCall : function(call){
            NotifyManager.sound('call');
            methods.updateMessenger({
                thread_id : call.thread_id,
                status : true,
                call_id : call.call_id,
                call_type : call.call_type,
                in_call : false,
                left_call : false
            });
            TippinManager.alert().Modal({
                wait_for_others : true,
                backdrop_ctrl : false,
                centered : true,
                theme : 'primary',
                icon : 'video',
                size : 'sm',
                title : 'Incoming video call',
                body : templates.call_alert(call),
                cb_btn_txt : 'Answer',
                cb_btn_icon : 'video',
                cb_btn_theme : 'success',
                callback : function(){
                    methods.joinCall(call, true)
                },
                cb_close : true,
                timer : 25000
            })
        },
        joinCall : function(call, add){
            if(opt.processing) return;
            opt.processing = true;
            let complete = function () {
                TippinManager.alert().destroyModal();
                methods.updateMessenger({
                    thread_id : call.thread_id,
                    status : true,
                    call_id : call.call_id,
                    call_type : call.call_type,
                    in_call : true,
                    left_call : false
                });
                methods.openCallWindow(call);
                NotifyManager.heartbeat();
                opt.processing = false;
            };
            if(add){
                TippinManager.alert().Modal({
                    size : 'sm',
                    icon : 'user-plus',
                    pre_loader : true,
                    centered : true,
                    unlock_buttons : false,
                    allow_close : false,
                    backdrop_ctrl : false,
                    title: 'Joining Call',
                    theme: 'info',
                    onReady : function () {
                        TippinManager.xhr().payload({
                            route : '/messenger/update/'+call.thread_id,
                            data : {
                                type : 'join_call'
                            },
                            success : complete,
                            fail : function(){
                                TippinManager.alert().destroyModal();
                                opt.processing = false;
                            },
                            bypass : true,
                            fail_alert : true
                        })
                    }
                });
            }
            else{
                complete()
            }
        },
        leaveCall : function(parent, call){
            if(!parent){
                if(opt.heartbeat_interval) clearInterval(opt.heartbeat_interval);
                if(opt.channel_status) opt.channel.unsubscribe();
                if(opt.call_type === 1) window.removeEventListener("beforeunload", methods.windowClosed, false);
            }
            TippinManager.xhr().payload({
                route : '/messenger/update/'+(parent ? call.thread_id : opt.thread_id),
                data : {
                    type : 'leave_call'
                },
                success : function(data){
                    if(parent){
                        if(TippinManager.common().modules.includes('ThreadManager')){
                            ThreadManager.Import().callStatus({
                                thread_id : call.thread_id,
                                status : data.count !== 0,
                                call_id : call.call_id,
                                call_type : call.call_type,
                                in_call : false,
                                left_call : true
                            })
                        }
                        NotifyManager.heartbeat();
                        return;
                    }
                    if(window.opener){
                        if(typeof window.opener.ThreadManager !== 'undefined'){
                            window.opener.ThreadManager.Import().callStatus({
                                thread_id : opt.thread_id,
                                status : data.count !== 0,
                                call_id : opt.call_id,
                                call_type : opt.call_type,
                                in_call : false,
                                left_call : true
                            })
                        }
                        if(typeof window.opener.NotifyManager !== 'undefined') window.opener.NotifyManager.heartbeat();
                    }
                    window.close();
                    setTimeout(function () {
                        window.close();
                        window.location.reload()
                    }, 2500)
                },
                fail : function(){
                    if(window.opener) window.close()
                }
            });
        },
        openCallWindow : function(call){
            let popUp = window.open('',call.call_id);
            if(!popUp || typeof popUp.closed === 'undefined' || popUp.closed ){
                TippinManager.alert().Alert({
                    close : true,
                    toast : true,
                    theme : 'error',
                    title : 'It appears your browser is blocking popups. Please allow popups from Tippindev to enable us to launch your video calls'
                });
                return;
            }
            if(popUp.location.href === 'about:blank') popUp.location.href = '/messenger/'+call.thread_id+'/call/'+call.call_id;
            popUp.focus()
        },
        endCall : function(){
            if(!opt.call_admin) return;
            if(opt.heartbeat_interval) clearInterval(opt.heartbeat_interval);
            TippinManager.xhr().payload({
                route : '/messenger/update/'+opt.thread_id,
                data : {
                    type : 'end_call'
                },
                success : function(){
                    opt.initialized = false;
                    if(opt.call_type === 1){
                        window.removeEventListener("beforeunload", methods.windowClosed, false);
                        window.close();
                    }
                    setTimeout(function () {
                        window.location.reload()
                    }, 3500)
                }
            })
        },
        callEnded : function(call){
            methods.updateMessenger({
                thread_id : call.thread_id,
                status : false,
                call_id : null,
                call_type : null,
                in_call : false,
                left_call : false
            });
            if(opt.initialized && opt.call_type === 1) window.removeEventListener("beforeunload", methods.windowClosed, false);
            if(opt.initialized && call.call_id === opt.call_id){
                if(opt.heartbeat_interval) clearInterval(opt.heartbeat_interval);
                setTimeout(function () {
                    window.location.reload()
                }, 3500)
            }
            if(window.opener && call.call_id === opt.call_id && TippinManager.common().modules.includes('WebRTCManager')){
                window.close()
            }
        },
        popupNoCall : function(){
            methods.callEnded(opt);
            TippinManager.alert().Alert({
                toast : true,
                theme : 'error',
                title : 'It appears that call is not available or does not exist'
            })
        }
    };
    return {
        init : mounted.Initialize,
        newCall : methods.incomingCall,
        join : methods.joinCall,
        leave : methods.leaveCall,
        endCall : methods.endCall,
        callEnded : methods.callEnded,
        popupNoCall : methods.popupNoCall,
        state : function () {
            return {
                initialized : opt.initialized,
                call : opt.call,
                call_id : opt.call_id,
                call_mode : opt.call_mode,
                call_type : opt.call_type,
                call_owner : opt.call_owner,
                call_admin : opt.call_admin,
                created_at : opt.created_at,
                thread_id : opt.thread_id,
                thread_name : opt.thread_name,
                thread_type : opt.thread_type,
                thread_admin : opt.thread_admin
            }
        },
        channel : function () {
            return {
                socket : opt.channel,
                state : opt.channel_status,
                profiles : opt.active_profiles,
                reconnected : Sockets.reconnected,
                disconnected : Sockets.disconnected
            }
        }
    };
}());
