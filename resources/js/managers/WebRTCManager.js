import adapter from 'webrtc-adapter';
let hark = require('hark');
window.WebRTCManager = (function () {
    var opt = {
        initialized : false,
        my_stream_ctnr : $("#my_video_ctrn"),
        other_stream_ctnr : $("#other_videos_ctrn"),
        rtc_socket : null
    },
    Init = {
        Setup : function () {
            if(opt.initialized) return;
            if($("#rtc_settings").length) TippinManager.alert().destroyModal();
            Init.Default();
            opt.initialized = true;
            if(CallManager.state().call_type === 1 && CallManager.state().thread_type === 1){
                opt.settings.video_constraints.width = {ideal : 1920};
                opt.settings.video_constraints.height = {ideal : 1080};
            }
            if(CallManager.state().thread_type === 2) opt.settings.active_speaker = true;
            Sockets.setup();
            UserMedia.getUserMedia('default', true,
                function(){
                    UserMedia.setupStream(true)},
                function(){
                    UserMedia.getUserMedia('individual', true)
            })
        },
        Default : function () {
            opt.initialized = false;
            opt.settings = {
                _mode : 'default',
                mode : 'default',
                audio : false,
                video : false,
                screen : false,
                muted : false,
                video_paused : false,
                myself_visible : true,
                others_visible : true,
                active_speaker : false,
                video_constraints : {
                    width : {ideal: 1280},
                    height : {ideal: 720},
                    facingMode: 'user'
                },
                loading : {
                    video : true,
                    audio : true,
                    permission_check : false,
                    screen : false
                }
            };
            opt.speech_events = {
                analyzer : null,
                speaker_locked : false,
                speaker_id : null,
                screen_share_id : null,
                speaker_timeout : null,
                activeLockId : function () {
                    return this.screen_share_id ? this.screen_share_id : this.speaker_id
                }
            };
            opt.local_storage  = {
                peers : [],
                peer_streams : [],
                audio : null,
                video : null,
                screen : null,
                broadcast_stream : null
            };
        }
    },
    Sockets = {
        setup : function(){
            opt.rtc_socket = CallManager.channel().socket;
            opt.rtc_socket.listenForWhisper('exchange', function(data){
                if(!opt.initialized) return;
                if(TippinManager.common().id === data.to) {
                    RTC.exchangeData(data)
                }
            })
            .listenForWhisper('need_offers', function(data){
                if(!opt.initialized) return;
                let peer = methods.locateStorageItem({type : 'peer', id : data.owner_id});
                if(peer.found) opt.local_storage.peers.splice(peer.index, 1);
                RTC.createRTCPeer(data, true)
            })
            .listenForWhisper('need_offer', function(data){
                if(!opt.initialized) return;
                if(data.to === TippinManager.common().id){
                    let peer = methods.locateStorageItem({type : 'peer', id : data.owner_id});
                    if(peer.found) opt.local_storage.peers.splice(peer.index, 1);
                    RTC.createRTCPeer(data, true)
                }
            })
            .listenForWhisper('peer_stream_state', function(data){
                if(!opt.initialized) return;
                methods.updateOtherState(data)
            })
            .listenForWhisper('need_stream_state', function(data){
                if(!opt.initialized) return;
                if(data.to === TippinManager.common().id){
                    methods.broadcastMyState(true, data.owner_id)
                }
            })
            .listenForWhisper('send_stream_state', function(data){
                if(!opt.initialized) return;
                if(data.to === TippinManager.common().id){
                    methods.updateOtherState(data)
                }
            })
            .listenForWhisper('speaking', function(data){
                if(!opt.initialized) return;
                methods.manageActiveSpeaker(data.owner_id, true)
            })
            .listenForWhisper('stopped_speaking', function(data){
                if(!opt.initialized) return;
                methods.manageActiveSpeaker(data.owner_id, false)
            })
            .listenForWhisper('screen_share_started', function(data){
                if(!opt.initialized) return;
                TippinManager.alert().Alert({
                    toast : true,
                    theme : 'info',
                    title : data.name,
                    body : 'has started sharing their screen. Consider maximizing their stream for best viewing results'
                })
            })
        },
        joining : function (user) {

        },
        leaving : function (user) {
            methods.peerLeaving(user)
        },
        disconnected : function(){
            //coming later
        },
        reconnected : function(){
            //coming later
        }
    },
    UserMedia = {
        showMediaHelpModal : function(){
            TippinManager.alert().Modal({
                icon : 'video',
                centered : true,
                unlock_buttons : false,
                backdrop_ctrl : false,
                title: 'Grant Audio/Video',
                theme: 'primary',
                h4 : false,
                body : '<div id="web_rtc_help" class="col-12 h5 mb-4"> For the best experience, please allow Tippindev to access your audio and video devices. ' +
                    'For more information, follow the links below that correspond to the browser you are currently using</div>' +
                    '<div class="col-12 text-center h4">' +
                    '<a class="mx-3" href="https://support.google.com/chrome/answer/2693767" target="_blank"><i class="fab fa-chrome"></i> Chrome</a> ' +
                    '<a class="mx-3" href="https://support.mozilla.org/en-US/kb/how-manage-your-camera-and-microphone-permissions" target="_blank"><i class="fab fa-firefox"></i> Firefox</a> ' +
                    '<a class="mx-3" href="https://support.apple.com/guide/safari/websites-ibrwe2159f50/mac" target="_blank"><i class="fab fa-safari"></i> Safari</a> ' +
                    '</div>'
            })
        },
        checkPermissionStatus : function(force){
            if(opt.settings.loading.permission_check) return;
            if(opt.settings.loading.video || opt.settings.loading.audio || force){
                UserMedia.showMediaHelpModal()
            }
            opt.settings.loading.permission_check = true;
        },
        getUserMedia : function(type, init, onCompleted, onFailed){
            switch(type){
                case 'default':
                    UserMedia.getMediaDefault(init, onCompleted, onFailed);
                    setTimeout(UserMedia.checkPermissionStatus, 10000);
                break;
                case 'individual':
                    if(opt.settings.loading.video && opt.settings.loading.audio){
                        let execute = function(){
                            UserMedia.getUserMedia('individual', init);
                        };
                        UserMedia.getMediaAudio(init, execute, execute);
                        UserMedia.getMediaVideo(init, execute, execute);
                    }
                    else if(!opt.settings.loading.video && !opt.settings.loading.audio){
                        TippinManager.alert().destroyModal();
                        opt.settings.mode = (opt.settings.video ? 'video' : opt.settings.audio ? 'audio' : 'empty');
                        if(opt.settings.mode === 'empty' && !opt.settings.loading.permission_check) UserMedia.checkPermissionStatus(true);
                        UserMedia.setupStream(init)
                    }
                break;
                case 'audio':
                    UserMedia.getMediaAudio(init, onCompleted, onFailed);
                break;
                case 'video':
                    UserMedia.getMediaVideo(init, onCompleted, onFailed);
                break;
                case 'screen':
                    UserMedia.getMediaScreenShare(onCompleted, onFailed);
                break;
            }
        },
        getMediaDefault : function(init, onCompleted, onFailed){
            opt.settings.loading.audio = true;
            opt.settings.loading.video = true;
            navigator.mediaDevices.getUserMedia({audio : true, video : opt.settings.video_constraints})
            .then(function(stream) {
                opt.local_storage.audio = stream.getAudioTracks()[0];
                opt.local_storage.video = stream.getVideoTracks()[0];
                opt.settings.loading.audio = false;
                opt.settings.loading.video = false;
                opt.settings.audio = true;
                opt.settings.video = true;
                TippinManager.alert().destroyModal();
                onCompleted()
            })
            .catch(function() {
                if(onFailed) onFailed()
            })
        },
        getMediaAudio : function(init, onCompleted, onFailed){
            opt.settings.loading.audio = true;
            navigator.mediaDevices.getUserMedia({audio : true})
            .then(function(audio) {
                opt.local_storage.audio = audio.getAudioTracks()[0];
                opt.settings.loading.audio = false;
                opt.settings.audio = true;
                onCompleted()
            })
            .catch(function() {
                opt.settings.loading.audio = false;
                opt.settings.audio = false;
                if(onFailed) onFailed()
            })
        },
        getMediaVideo : function(init, onCompleted, onFailed){
            opt.settings.loading.video = true;
            navigator.mediaDevices.getUserMedia({video : opt.settings.video_constraints})
            .then(function(video) {
                opt.local_storage.video = video.getVideoTracks()[0];
                opt.settings.loading.video = false;
                opt.settings.video = true;
                onCompleted()
            })
            .catch(function() {
                opt.settings.loading.video = false;
                opt.settings.video = false;
                if(onFailed) onFailed()
            })
        },
        getMediaScreenShare : function (onCompleted, onFailed) {
            if(typeof navigator.mediaDevices.getDisplayMedia !== 'function'){
                if(onFailed) onFailed();
                return;
            }
            opt.settings.loading.screen = true;
            navigator.mediaDevices.getDisplayMedia({video: true})
            .then(function(screen) {
                opt.local_storage.screen = screen.getVideoTracks()[0];
                opt.settings.loading.screen = false;
                opt.settings.screen = true;
                onCompleted()
            })
            .catch(function() {
                opt.settings.loading.screen = false;
                opt.settings.screen = false;
                if(onFailed) onFailed()
            });
        },
        setupStream : function (init) {
            UserMedia.makeMyStream();
            UserMedia.showMyStream();
            methods.toolbarState();
            if(init){
                opt.settings._mode = opt.settings.mode;
                if((!opt.local_storage.video || !opt.local_storage.audio) && opt.settings.mode !== 'screen'){
                    let body;
                    if(!opt.local_storage.video && !opt.local_storage.audio) body = 'audio or video devices.';
                    else if(!opt.local_storage.video) body = 'video device.';
                    else if(!opt.local_storage.audio) body = 'audio device.';
                    TippinManager.alert().Alert({
                        toast : true,
                        title : 'Error',
                        theme : 'warning',
                        body : 'Unable to load your '+body+' Proceeding without'
                    });
                }
                methods.makeOffers()
            }
        },
        activateVoiceActivity : function(){
            if(CallManager.state().thread_type === 1) return;
            try{
                opt.speech_events.analyzer = hark(opt.local_storage.broadcast_stream);
                opt.speech_events.analyzer.on('speaking', function() {
                    if(!CallManager.channel().state) return;
                    opt.rtc_socket.whisper('speaking', {
                        owner_id : TippinManager.common().id
                    })
                })
                .on('stopped_speaking', function() {
                    if(!CallManager.channel().state) return;
                    opt.rtc_socket.whisper('stopped_speaking', {
                        owner_id : TippinManager.common().id
                    })
                })
            }catch (e) {
                console.log(e)
            }

        },
        makeMyStream : function(){
            let streams = [];
            switch (opt.settings.mode) {
                case 'default':
                    if(opt.local_storage.audio) streams.push(opt.local_storage.audio);
                    if(opt.local_storage.video) streams.push(opt.local_storage.video);
                break;
                case 'audio':
                    if(opt.local_storage.audio) streams.push(opt.local_storage.audio);
                break;
                case 'video':
                    if(opt.local_storage.video) streams.push(opt.local_storage.video);
                break;
                case 'screen':
                    if(opt.local_storage.audio) streams.push(opt.local_storage.audio);
                    if(opt.local_storage.screen) streams.push(opt.local_storage.screen);
                break;
            }
            opt.local_storage.broadcast_stream = (streams.length ? new MediaStream(streams) : null);
            if(opt.local_storage.broadcast_stream){
                if(opt.speech_events.analyzer) opt.speech_events.analyzer.stop();
                UserMedia.activateVoiceActivity()
            }
        },
        showMyStream : function () {
            if(!['default', 'video', 'audio', 'screen'].includes(opt.settings.mode)) return;
                let template;
                if(opt.settings.mode === 'default'){
                    template = (opt.settings.video_paused ? templates.my_audio_stream() : templates.my_video_stream())
                }
                else if(opt.settings.mode === 'video'){
                    template = (opt.settings.video_paused ? templates.my_audio_stream() : templates.my_video_stream())
                }
                else if(opt.settings.mode === 'screen'){
                    template = templates.my_video_stream()
                }
                else{
                    template = templates.my_audio_stream()
                }
                opt.my_stream_ctnr.html(template);
                let myStream = document.getElementById('my_stream_src');
                methods.addSrcStream(myStream, opt.local_storage.broadcast_stream);
                opt.settings.myself_visible ? opt.my_stream_ctnr.show() : opt.my_stream_ctnr.hide();
        },
        shareScreen : function (onComplete) {
            opt.settings.mode = 'screen';
            UserMedia.makeMyStream();
            opt.local_storage.screen.onended = function () {
                UserMedia.stopScreenShare(onComplete)
            };
            UserMedia.showMyStream();
            if(CallManager.state().call_mode !== 4){
                RTC.updatePeers();
                methods.broadcastMyState(false);
                opt.rtc_socket.whisper('screen_share_started', {
                    name : TippinManager.common().name
                });
            }
            onComplete()
        },
        stopScreenShare : function (onComplete) {
            opt.local_storage.screen.stop();
            opt.local_storage.screen = null;
            opt.settings.screen = false;
            opt.settings.mode = opt.settings._mode;
            UserMedia.makeMyStream();
            UserMedia.showMyStream();
            if(CallManager.state().call_mode !== 4){
                RTC.updatePeers();
                methods.broadcastMyState(false);
            }
            onComplete()
        }
    },
    RTC = {
        updatePeers : function () {
            switch (opt.settings.mode) {
                case 'default':
                    let default_audio = opt.local_storage.broadcast_stream.getAudioTracks(),
                        default_video = opt.local_storage.broadcast_stream.getVideoTracks();
                    opt.local_storage.peers.forEach(function (peer) {
                        let senderVideo = null, senderAudio = null;
                        if(default_video.length){
                            senderVideo = peer.RTCPeer.getSenders().find(function(s) {
                                return s.track.kind === default_video[0].kind;
                            })
                        }
                        if(default_audio.length){
                            senderAudio = peer.RTCPeer.getSenders().find(function(x) {
                                return x.track.kind === default_audio[0].kind;
                            })
                        }
                        if(senderVideo){
                            senderVideo.replaceTrack(default_video[0])
                        }
                        if(senderAudio){
                            senderAudio.replaceTrack(default_audio[0])
                        }
                        if(!senderAudio || !senderVideo){
                            let profile = methods.locateStorageItem({type : 'profile', id : peer.owner_id});
                            RTC.createRTCPeer(profile.item, true)
                        }
                    });
                break;
                case 'screen':
                    let screen_feed = opt.local_storage.broadcast_stream.getVideoTracks()[0], renew_screen = [];
                    opt.local_storage.peers.forEach(function (peer, index) {
                        let peerID = peer.owner_id, senderScreen = peer.RTCPeer.getSenders().find(function(s) {
                            return s.track && s.track.kind === screen_feed.kind;
                        });
                        if(senderScreen){
                            senderScreen.replaceTrack(screen_feed)
                        }
                        else{
                            renew_screen.push([index, peerID])
                        }
                    });
                    if(renew_screen.length){
                        renew_screen.forEach(function (value) {
                            opt.local_storage.peers.splice(value[0], 1);
                            opt.rtc_socket.whisper('need_offer', {
                                to :  value[1],
                                owner_id : TippinManager.common().id,
                                avatar : TippinManager.common().slug,
                                name : TippinManager.common().name
                            })
                        })
                    }
                break;
                case 'audio':
                    let audio_feed = opt.local_storage.broadcast_stream.getAudioTracks()[0], renew_audio = [];
                    opt.local_storage.peers.forEach(function (peer, index) {
                        let peerID = peer.owner_id, sendTracks = peer.RTCPeer.getSenders(), senderAudio = null;
                        if(sendTracks.length){
                            senderAudio = peer.RTCPeer.getSenders().find(function(s) {
                                return s.track && s.track.kind === audio_feed.kind;
                            });
                            if(sendTracks.length === 1 && senderAudio){
                                senderAudio.replaceTrack(audio_feed)
                            }
                            else{
                                opt.local_storage.peers.splice(index, 1);
                                opt.rtc_socket.whisper('need_offer', {
                                    to : peerID,
                                    owner_id : TippinManager.common().id,
                                    avatar : TippinManager.common().slug,
                                    name : TippinManager.common().name
                                })
                            }
                        }
                        else{
                            opt.local_storage.peers.splice(index, 1);
                            opt.rtc_socket.whisper('need_offer', {
                                to : peerID,
                                owner_id : TippinManager.common().id,
                                avatar : TippinManager.common().slug,
                                name : TippinManager.common().name
                            })
                        }
                    });
                break;
            }
        },
        createRTCPeer : function(user, isOffer){
            const peer = new RTCPeerConnection({
                iceServers: [
                    {
                        urls : [
                            "stun:stun.l.google.com:19302",
                            "stun:stun1.l.google.com:19302",
                            "stun:stun2.l.google.com:19302"
                        ]
                    },
                ]}
            );
            peer.onnegotiationneeded = function(){
                if (isOffer) {
                    peer.createOffer({offerToReceiveVideo: true, offerToReceiveAudio : true}).then(function(offer){
                        peer.setLocalDescription(offer).then(function(){
                            opt.rtc_socket.whisper('exchange', {
                                owner_id : TippinManager.common().id,
                                to : user.owner_id,
                                sdp : peer.localDescription
                            })
                        });
                    })
                }
            };
            if(opt.local_storage.broadcast_stream){
                for (const track of opt.local_storage.broadcast_stream.getTracks()) {
                    peer.addTrack(track, opt.local_storage.broadcast_stream);
                }
            }
            peer.ontrack = event => {
                if(Array.isArray(event.streams) && event.streams.length){
                    $("#empty_room").hide();
                    let peerStream = methods.locateStorageItem({type : 'stream', id : user.owner_id});
                    peerStream.found ? peerStream.item.stream = event.streams[0] : opt.local_storage.peer_streams.push({owner_id : user.owner_id, stream : event.streams[0]});
                        let template, senderAudio = event.streams[0].getTracks().find(function(s) {
                            return "kind" in s && s.kind === 'audio';
                        }),
                        senderVideo = event.streams[0].getTracks().find(function(x) {
                            return "kind" in x && x.kind === 'video';
                        });
                        if(senderVideo){
                            template = templates.other_video_stream(user)
                        }
                        else if(senderAudio){
                            template = templates.other_audio_stream(user)
                        }
                        let otherStream = $('#other_stream_ctnr_'+user.owner_id);
                        otherStream.length ? otherStream.replaceWith(template) : opt.other_stream_ctnr.append(template);
                        methods.checkActiveSpeaker();
                        opt.settings.others_visible ? opt.other_stream_ctnr.show() : opt.other_stream_ctnr.hide();
                        let elm = document.getElementById('other_stream_src_'+user.owner_id);
                        methods.addSrcStream(elm, event.streams[0])
                }
                opt.rtc_socket.whisper('need_stream_state', {
                    owner_id : TippinManager.common().id,
                    to : user.owner_id
                });
                PageListeners.listen().tooltips()
            };
            peer.onicecandidate = event => {
                if (event.candidate) {
                    opt.rtc_socket.whisper('exchange', {
                        owner_id : TippinManager.common().id,
                        to : user.owner_id,
                        candidate : event.candidate
                    });
                }
            };
            peer.onconnectionstatechange = function() {
                if(['disconnected', 'failed'].includes(peer.connectionState)){
                    methods.peerLeaving(user)
                }
            };
            let locate_peer = methods.locateStorageItem({type : 'peer', id : user.owner_id});
            if(locate_peer.found){
                opt.local_storage.peers[locate_peer.index].RTCPeer = peer;
                return opt.local_storage.peers[locate_peer.index].RTCPeer
            }
            else{
                opt.local_storage.peers.push({
                    owner_id : user.owner_id,
                    RTCPeer : peer
                });
                return opt.local_storage.peers[opt.local_storage.peers.length-1].RTCPeer
            }
        },
        exchangeData : function(data) {
            let peer, locate_peer = methods.locateStorageItem({type : 'peer', id : data.owner_id});
            if(locate_peer.found){
                peer = locate_peer.item.RTCPeer
            }
            else{
                let profile = methods.locateStorageItem({type : 'profile', id : data.owner_id});
                peer = RTC.createRTCPeer(profile.item, false);
            }
            if(data.sdp){
                let sdp = new RTCSessionDescription(data.sdp);
                peer.setRemoteDescription(sdp).then(function(){
                    if(peer.remoteDescription.type === 'offer'){
                        peer.createAnswer().then((desc) => {
                            peer.setLocalDescription(desc).then(() => {
                                opt.rtc_socket.whisper('exchange', {
                                    owner_id : TippinManager.common().id,
                                    to : data.owner_id,
                                    sdp : peer.localDescription
                                })
                            });
                        })
                    }
                })
                .catch(function(){
                    console.log("Remote description failed")
                })
            }
            else {
                peer.addIceCandidate(new RTCIceCandidate(data.candidate)).then(function () {

                }).catch(function () {
                    console.log("ICE failed")
                });
            }
        }
    },
    methods = {
        broadcastMyState : function(single, to){
            if(CallManager.state().call_mode === 4) return;
            let data = {
                owner_id : TippinManager.common().id,
                name : TippinManager.common().name,
                avatar : TippinManager.common().slug,
                mode : opt.settings.mode,
                muted : opt.settings.muted,
                video_paused : opt.settings.video_paused
            };
            if(single){
                data.to = to;
                opt.rtc_socket.whisper('send_stream_state', data)
            }
            else{
                opt.rtc_socket.whisper('peer_stream_state', data)
            }
        },
        unlockActiveSpeaker : function(empty){
            if(empty) opt.speech_events.speaker_id = null;
            opt.speech_events.speaker_locked = false;
            if(opt.speech_events.speaker_timeout) clearTimeout(opt.speech_events.speaker_timeout);
            opt.speech_events.speaker_timeout = null;
        },
        checkActiveSpeaker : function(){
            let streams = $(".other_stream_ctnr");
            if(!opt.settings.active_speaker){
                methods.unlockActiveSpeaker(true);
                if(CallManager.state().thread_type === 2){
                    opt.other_stream_ctnr.addClass('row mx-1');
                    streams.addClass('col-12 col-md-6 col-xl-4')
                }
                streams.show();
                return;
            }
            if(CallManager.state().thread_type === 2){
                opt.other_stream_ctnr.removeClass('row mx-1');
                streams.removeClass('col-12 col-md-6 col-xl-4');
            }
            if(!opt.speech_events.activeLockId()){
                streams.hide();
                if(opt.local_storage.peer_streams.length){
                    $("#other_stream_ctnr_"+opt.local_storage.peer_streams[0].owner_id).show()
                }
                else{
                    streams.show()
                }
            }
            else{
                streams.hide();
                let locate_stream = methods.locateStorageItem({type : 'stream', id : opt.speech_events.activeLockId()});
                if(locate_stream.found){
                    $("#other_stream_ctnr_"+locate_stream.item.owner_id).show()
                }
                else{
                    opt.speech_events.speaker_id = null;
                    methods.checkActiveSpeaker()
                }
            }
        },
        manageActiveSpeaker : function(owner, speaking){
            if(!opt.settings.active_speaker) return;
            if(opt.speech_events.speaker_locked){
                // if(!speaking && opt.speech_events.speaker_id === owner){
                //     methods.unlockActiveSpeaker(false);
                //     methods.checkActiveSpeaker()
                // }
            }
            else{
                methods.unlockActiveSpeaker(true);
                opt.speech_events.speaker_id = owner;
                opt.speech_events.speaker_locked = true;
                opt.speech_events.speaker_timeout = setTimeout(methods.unlockActiveSpeaker, 5000);
                methods.checkActiveSpeaker()
            }
        },
        updateOtherState : function(data){
            let vid_tog = $("#other_stream_video_toggle_"+data.owner_id), audio_tog = $("#other_stream_audio_toggle_"+data.owner_id),
                other_voice_state = $("#other_avatar_audio_"+data.owner_id);
            switch(data.mode){
                case 'default':
                    if(opt.speech_events.screen_share_id === data.owner_id){
                        opt.speech_events.screen_share_id = null;
                    }
                    if(data.video_paused){
                        vid_tog.hide();
                        audio_tog.show();
                        if(other_voice_state.length){
                            data.muted ?
                                other_voice_state.removeClass('avatar-is-online').addClass('avatar-is-away')
                                : other_voice_state.removeClass('avatar-is-away').addClass('avatar-is-online')
                        }
                    }
                    else{
                        vid_tog.show();
                        audio_tog.hide()
                    }
                break;
                case 'audio':
                    if(opt.speech_events.screen_share_id === data.owner_id){
                        opt.speech_events.screen_share_id = null;
                    }
                    if(vid_tog.length){
                        let audioStream = methods.locateStorageItem({type : 'stream', id : data.owner_id}), elm = $("#other_stream_ctnr_"+data.owner_id);
                        elm.replaceWith(templates.other_audio_stream(data));
                        methods.addSrcStream(document.getElementById('other_stream_src_'+data.owner_id), audioStream.item.stream)
                    }
                    data.muted ? other_voice_state.removeClass('avatar-is-online').addClass('avatar-is-away')
                    : other_voice_state.removeClass('avatar-is-away').addClass('avatar-is-online');
                break;
                case 'screen':
                    opt.speech_events.screen_share_id = data.owner_id;
                    if(vid_tog.length){
                        vid_tog.show();
                        audio_tog.hide();
                    }
                    else{
                        let screenStream = methods.locateStorageItem({type : 'stream', id : data.owner_id}), elm = $("#other_stream_ctnr_"+data.owner_id);
                        elm.length ? elm.replaceWith(templates.other_video_stream(data)) : opt.other_stream_ctnr.append(templates.other_audio_stream(data));
                        methods.addSrcStream(document.getElementById('other_stream_src_'+data.owner_id), screenStream.item.stream)
                    }
                    methods.checkActiveSpeaker();
                break;
            }
            PageListeners.listen().tooltips()
        },
        addSrcStream : function(elm, stream){
            try{
                if("srcObject" in elm){
                    elm.srcObject = stream;
                }
                else{
                    elm.src = window.URL.createObjectURL(stream);
                }
            } catch {
                TippinManager.alert().Alert({
                    toast : true,
                    theme : 'error',
                    title : 'Stream Error',
                    body : 'Unable to add a stream to your page'
                })
            }
        },
        updateState : function(arg){
            switch(arg.action){
                case 'toggle_my_stream_visible':
                    if(opt.settings.myself_visible){
                        opt.settings.myself_visible = false;
                        opt.my_stream_ctnr.hide();
                    }
                    else{
                        opt.settings.myself_visible = true;
                        opt.my_stream_ctnr.show();
                    }
                break;
                case 'toggle_other_stream_visible':
                    if(opt.settings.others_visible){
                        opt.settings.others_visible = false;
                        opt.other_stream_ctnr.hide()
                    }
                    else{
                        opt.settings.others_visible = true;
                        opt.other_stream_ctnr.show()
                    }
                break;
                case 'toggle_active_speaker':
                    opt.settings.active_speaker = !opt.settings.active_speaker;
                    methods.checkActiveSpeaker();
                break;
                case 'disable_vid':
                    if(['default', 'video'].includes(opt.settings.mode)){
                        opt.settings.video_paused = true;
                        opt.local_storage.video.enabled = false;
                        methods.broadcastMyState(false);
                        UserMedia.showMyStream()
                    }
                break;
                case 'enable_vid':
                    if(['default', 'video'].includes(opt.settings.mode)){
                        opt.settings.video_paused = false;
                        opt.local_storage.video.enabled = true;
                        methods.broadcastMyState(false);
                        UserMedia.showMyStream();
                    }
                break;
                case 'mute_mic':
                    if(['default', 'audio', 'screen'].includes(opt.settings.mode) && opt.settings.audio){
                        opt.settings.muted = true;
                        opt.local_storage.audio.enabled = false;
                        let mute_elm = $('#my_avatar_audio');
                        if(mute_elm.length) mute_elm.removeClass('avatar-is-online').addClass('avatar-is-away');
                        methods.broadcastMyState(false);
                    }
                break;
                case 'unmute_mic':
                    if(['default', 'audio', 'screen'].includes(opt.settings.mode) && opt.settings.audio){
                        opt.settings.muted = false;
                        opt.local_storage.audio.enabled = true;
                        let mute_elm = $('#my_avatar_audio');
                        if(mute_elm.length) mute_elm.removeClass('avatar-is-away').addClass('avatar-is-online');
                        methods.broadcastMyState(false);
                    }
                break;
                case 'share_screen':
                    return UserMedia.getUserMedia('screen', null, function () {
                        UserMedia.shareScreen(methods.toolbarState)
                    }, function () {
                        TippinManager.alert().Alert({
                            toast : true,
                            title : 'Error',
                            theme : 'error',
                            body : 'Unable to load your screen to share'
                        })
                    });
                case 'stop_screen':
                    return UserMedia.stopScreenShare(methods.toolbarState);
                default : return;
            }
            methods.toolbarState()
        },
        peerLeaving : function(user){
            let locate_peer = methods.locateStorageItem({type : 'peer', id : user.owner_id}),
            locate_stream = methods.locateStorageItem({type : 'stream', id : user.owner_id});
            if(locate_peer.found){
                locate_peer.item.RTCPeer.close();
                opt.local_storage.peers.splice(locate_peer.index, 1)
            }
            if(locate_stream.found) opt.local_storage.peer_streams.splice(locate_stream.index, 1);
            $("#other_stream_ctnr_"+user.owner_id).remove();
            if(!opt.local_storage.peer_streams.length) $("#empty_room").show();
            methods.checkActiveSpeaker()
        },
        makeOffers : function(){
            if(!CallManager.channel().state){
                setTimeout(methods.makeOffers, 0);
                return;
            }
            if(opt.local_storage.broadcast_stream && opt.settings.video && opt.settings.audio){
                CallManager.channel().profiles.forEach(function(user){
                    RTC.createRTCPeer(user, true)
                })
            }
            else{
                opt.rtc_socket.whisper('need_offers', {
                    owner_id : TippinManager.common().id,
                    avatar : TippinManager.common().slug,
                    name : TippinManager.common().name
                })
            }
        },
        locateStorageItem : function(arg){
            let collection, term,
                item = {
                    found : false,
                    index : 0,
                    item : null
                };
            switch(arg.type){
                case 'peer':
                    collection = opt.local_storage.peers;
                    term = 'owner_id';
                break;
                case 'profile':
                    collection = CallManager.channel().profiles;
                    term = 'owner_id';
                break;
                case 'stream':
                    collection = opt.local_storage.peer_streams;
                    term = 'owner_id';
                break;
            }
            for(let i = 0; i < collection.length; i++) {
                if (collection[i][term] === arg.id) {
                    item.found = true;
                    item.index = i;
                    item.item = collection[i];
                    break;
                }
            }
            return item
        },
        stopMyStream : function(){
            if(opt.local_storage.broadcast_stream) opt.local_storage.broadcast_stream.getTracks().forEach(track => track.stop());
        },
        closeOtherPeers : function(){
            opt.other_stream_ctnr.html('');
            opt.local_storage.peers.forEach(function (peer) {
                peer.RTCPeer.close()
            });
        },
        hangUp : function(end){
            TippinManager.button().addLoader({id : (end ? '#end_call_btn' : '#hang_up_btn')});
            methods.stopMyStream();
            methods.closeOtherPeers();
            end ? CallManager.endCall() : CallManager.leave(false)
        },
        closeOut : function () {
            methods.stopMyStream();
            methods.closeOtherPeers();
            Init.Default();
            methods.toolbarState();
            opt.other_stream_ctnr.html('').hide();
            opt.my_stream_ctnr.html('').hide();
            if($("#rtc_settings").length) TippinManager.alert().destroyModal();
        },
        settings : function () {
            TippinManager.alert().Modal({
                theme : 'dark',
                icon : 'cog',
                title: 'Call Settings',
                h4: false,
                unlock_buttons : false,
                centered : true,
                body : templates.settings(),
                onReady: function () {
                    $("#toggle_my_stream").change(function(){
                        $(this).is(':checked') ? $(this).closest('tr').addClass('bg-light') : $(this).closest('tr').removeClass('bg-light');
                        methods.updateState({
                            action : 'toggle_my_stream_visible'
                        })
                    });
                    $("#toggle_other_stream").change(function(){
                        $(this).is(':checked') ? $(this).closest('tr').addClass('bg-light') : $(this).closest('tr').removeClass('bg-light');
                        methods.updateState({
                            action : 'toggle_other_stream_visible'
                        })
                    });
                    $("#toggle_active_speaker").change(function(){
                        $(this).is(':checked') ? $(this).closest('tr').addClass('bg-light') : $(this).closest('tr').removeClass('bg-light');
                        methods.updateState({
                            action : 'toggle_active_speaker'
                        })
                    })
                }
            })
        },
        toolbarState : function(){
            let rtc_opt = $(".rtc_nav_opt"), rtc_vid = $(".rtc_nav_video"), rtc_audio = $(".rtc_nav_audio"), rtc_screen = $(".rtc_nav_screen"),
                rtc_vid_on = $(".rtc_video_on"), rtc_vid_off = $(".rtc_video_off"), rtc_audio_on = $(".rtc_audio_on"), rtc_audio_off = $(".rtc_audio_off"),
                rtc_screen_on = $(".rtc_screen_on"), rtc_screen_off = $(".rtc_screen_off");
            rtc_opt.hide();
            switch(opt.settings.mode){
                case 'default':
                    if(opt.settings.video_paused){
                        rtc_vid.show();
                        rtc_vid_off.show();
                        rtc_audio.show();
                        opt.settings.muted ? rtc_audio_off.show() : rtc_audio_on.show();
                        rtc_screen.show();
                        rtc_screen_off.show();
                    }
                    else if(opt.settings.video && opt.settings.muted){
                        rtc_vid.show();
                        rtc_vid_on.show();
                        rtc_audio.show();
                        rtc_audio_off.show();
                        rtc_screen.show();
                        rtc_screen_off.show();
                    }
                    else if(opt.settings.video && opt.settings.audio){
                        rtc_vid.show();
                        rtc_vid_on.show();
                        rtc_audio.show();
                        rtc_audio_on.show();
                        rtc_screen.show();
                        rtc_screen_off.show();
                    }
                break;
                case 'video':
                    if(opt.settings.video_paused){
                        rtc_vid.show();
                        rtc_vid_off.show();
                        rtc_screen.show();
                        rtc_screen_off.show();
                    }
                    else{
                        rtc_vid.show();
                        rtc_vid_on.show();
                        rtc_screen.show();
                        rtc_screen_off.show();
                    }
                break;
                case 'audio':
                    if(opt.settings.muted){
                        rtc_audio.show();
                        rtc_audio_off.show();
                        rtc_screen.show();
                        rtc_screen_off.show();
                    }
                    else{
                        rtc_audio.show();
                        rtc_audio_on.show();
                        rtc_screen.show();
                        rtc_screen_off.show();
                    }
                break;
                case 'screen':
                    rtc_screen.show();
                    rtc_screen_on.show();
                    if(opt.settings.audio){
                        rtc_audio.show();
                        opt.settings.muted ? rtc_audio_off.show() : rtc_audio_on.show()
                    }
                break;
            }
            PageListeners.listen().tooltips()
        }
    },
    templates = {
        my_video_stream : function () {
            return '<div class="shadow-sm rounded w-100 mx-auto embed-responsive embed-responsive-16by9" id="my_stream_call">' +
                '<video id="my_stream_src" muted autoplay playsinline class="embed-responsive-item"></video>' +
                '</div>'
        },
        my_audio_stream : function () {
            return '<div class="mx-auto" id="my_stream_call">' +
                '<div class="text-center text-secondary">' +
                '<img id="my_avatar_audio" class="medium-image-call rounded-circle avatar-is-'+(opt.settings.muted ? 'away' : 'online')+'" src="'+TippinManager.common().slug+'" /></div> ' +
                '<audio id="my_stream_src" muted autoplay playsinline class="NS"></audio>' +
                '</div>'
        },
        other_video_stream : function (user) {
            return '<div '+(opt.settings.active_speaker ? 'style="display: none;"' : '')+' class="'+(CallManager.state().thread_type === 2 && !opt.settings.active_speaker ? 'col-12 col-md-6 col-lg-4' : '')+' mt-2 mb-4 other_stream_ctnr" id="other_stream_ctnr_'+user.owner_id+'">' +
                    '<div class="col-12 text-center h4"><span class="badge badge-pill badge-light shadow">'+user.name+'</span> </div>'+
                        '<div class="group_stream w-100 mx-auto embed-responsive embed-responsive-16by9" id="other_stream_video_toggle_'+user.owner_id+'">' +
                        '<video id="other_stream_src_'+user.owner_id+'" autoplay playsinline controls class="embed-responsive-item"></video>' +
                        '</div>'+
                        '<div class="mx-auto NS" id="other_stream_audio_toggle_'+user.owner_id+'">' +
                        '<div class="text-center">' +
                        '<img id="other_avatar_audio_'+user.owner_id+'" width="200" height="200" class="rounded-circle avatar-is-online" src="'+user.avatar+'" /></div> ' +
                        '</div>'+
                    '</div>'
        },
        other_audio_stream : function (user) {
            return '<div '+(opt.settings.active_speaker ? 'style="display: none;"' : '')+' class="'+(CallManager.state().thread_type === 2 && !opt.settings.active_speaker ? 'col-12 col-md-6 col-lg-4' : '')+' mt-2 mb-4 other_stream_ctnr" id="other_stream_ctnr_'+user.owner_id+'">' +
                '<div class="col-12 text-center h4"><span class="badge badge-pill badge-light shadow">'+user.name+'</span> </div>'+
                '<div class="mx-auto" id="other_stream_audio_toggle_'+user.owner_id+'">' +
                '<div class="text-center">' +
                '<img id="other_avatar_audio_'+user.owner_id+'" width="200" height="200" class="rounded-circle avatar-is-online" src="'+user.avatar+'" /></div> ' +
                '<audio id="other_stream_src_'+user.owner_id+'" autoplay playsinline class="NS"></audio>' +
                '</div>' +
                '</div>'
        },
        settings : function(){
            let other_stream = '<tr class="'+(opt.settings.others_visible ? 'bg-light' : '')+'">\n' +
                '<td class="pointer_area" onclick="$(\'#toggle_other_stream\').click()"><div class="h4 mt-1"><i class="fas fa-caret-right"></i> Show others in call</div></td>\n' +
                '<td><div class="mt-1 float-right"><span class="switch switch-sm mt-1"><input class="switch switch_input" id="toggle_other_stream" name="toggle_other_stream" type="checkbox" '+(opt.settings.others_visible ? 'checked' : '')+'/><label for="toggle_other_stream"></label></span></div></td>\n' +
                '</tr>',
            active_speaker = '<tr class="'+(opt.settings.active_speaker ? 'bg-light' : '')+'">\n' +
                '<td class="pointer_area" onclick="$(\'#toggle_active_speaker\').click()"><div class="h4 mt-1"><i class="fas fa-caret-right"></i> Show active speaker only</div></td>\n' +
                '<td><div class="mt-1 float-right"><span class="switch switch-sm mt-1"><input class="switch switch_input" id="toggle_active_speaker" name="toggle_active_speaker" type="checkbox" '+(opt.settings.active_speaker ? 'checked' : '')+'/><label for="toggle_active_speaker"></label></span></div></td>\n' +
                '</tr>';
            return '<table id="rtc_settings" class="table mb-0 table-sm table-hover"><tbody>\n' +
                (opt.initialized ?
                    '<tr class="'+(opt.settings.myself_visible ? 'bg-light' : '')+'">\n' +
                    '<td class="pointer_area" onclick="$(\'#toggle_my_stream\').click()"><div class="h4 mt-1"><i class="fas fa-caret-right"></i> Show myself in call</div></td>\n' +
                    '<td><div class="mt-1 float-right"><span class="switch switch-sm mt-1"><input class="switch switch_input" id="toggle_my_stream" name="toggle_my_stream" type="checkbox" '+(opt.settings.myself_visible ? 'checked' : '')+'/><label for="toggle_my_stream"></label></span></div></td>\n' +
                    '</tr>\n'+
                    (CallManager.state().thread_type === 2 ? active_speaker : '')
                    : '')+
                '</tbody></table>'+
                '<div class="col-12 mt-4 text-center"><hr><button onclick="WebRTCManager.showHelpModal()" class="btn btn-sm btn-primary"><i class="far fa-question-circle"></i> Help enabling video?</button> </div>'
        }
    };
    return {
        setup : Init.Setup,
        hangUp : methods.hangUp,
        peerJoin : Sockets.joining,
        peerLeave : Sockets.leaving,
        changeState : methods.updateState,
        callSettings : methods.settings,
        shutdown : methods.closeOut,
        showHelpModal : UserMedia.showMediaHelpModal,
        state : function(){
            return {
                mode : opt.settings.mode,
                video : opt.settings.video,
                audio : opt.settings.audio,
                screen : opt.settings.screen,
                muted : opt.settings.muted,
                video_paused : opt.settings.video_paused,
                show_my_stream : opt.settings.myself_visible,
                show_other_stream : opt.settings.others_visible
            }
        },
        socket : function () {
            return {
                onDisconnect : Sockets.disconnected,
                onReconnect : Sockets.reconnected
            }
        }
    };
}());
