window.ThreadTemplates = (function () {
    var methods = {
        makeLinks : function(body){
            return autolinker.link(body)
        },
        makeYoutube : function(body){
            let regExp = /https?:\/\/(?:[0-9A-Z-]+\.)?(?:youtu\.be\/|youtube(?:-nocookie)?\.com\S*?[^\w\s-])([\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:['"][^<>]*>|<\/a>))[?=&+%\w.-]*/ig;
            return body.replace(regExp,'<div class="embed-responsive embed-responsive-16by9 my-2"><iframe allowfullscreen class="embed-responsive-item" src="https://www.youtube.com/embed/$1"></iframe></div>');
        },
        switch_mobile_view : function (power) {
            let nav = $("#RT_navbar"), main_section = $("#RT_main_section"), msg_sidebar = $("#message_sidebar_container"), msg_content = $("#message_content_container");
            if(power){
                nav.addClass('NS');
                main_section.removeClass('pt-5 mt-4').addClass('pt-0 mt-3');
                msg_sidebar.addClass('NS');
                msg_content.removeClass('NS');
            }
            else{
                nav.removeClass('NS');
                main_section.removeClass('pt-0 mt-3').addClass('pt-5 mt-4');
                msg_sidebar.removeClass('NS');
                msg_content.addClass('NS');
            }
        }
    },
    templates = {
        archive_thread_warning : function(data){
            if(data.type === 1){
                return 'Delete the conversation between you and <strong>'+data.name+'</strong>?'+
                       '<div class="card mt-3"><div class="card-body bg-warning shadow rounded"><h5>All '+data.messages+
                       ' messages between you and '+data.name+' will be removed. Any new messages will create a new conversation</h5></div></div>'
            }
            return 'Delete <strong>'+data.name+'</strong>?'+
                   '<div class="card mt-3"><div class="card-body bg-warning shadow rounded"><h5>All '+data.messages+
                   ' messages and '+data.participants+' participants will be removed. This group will no longer be accessible</h5></div></div>'
        },
        messages_disabled_overlay : function(){
            return '<div id="messaging_disabled_overlay" class="disabled-message-overlay rounded text-center">' +
                '<div class="mt-3 pt-1 h4"><span class="badge badge-pill badge-light"><i class="fas fa-comment-slash"></i> Messaging Disabled</span></div> ' +
                '</div>'
        },
        empty_base : function(){
            return '<div class="container h-100">\n' +
                '    <div class="row align-items-end h-100">\n' +
                '        <div class="col-12 text-center mb-5">\n' +
                '            <button data-toggle="tooltip" title="Search Profiles" data-placement="top" onclick="ThreadManager.load().search()" class="btn btn-outline-primary btn-circle btn-circle-xl mx-4 my-2"><i class="fas fa-search fa-3x"></i></button>\n' +
                '            <button data-toggle="tooltip" title="Create Group" data-placement="top" onclick="ThreadManager.load().createGroup()" class="btn btn-outline-success btn-circle btn-circle-xl mx-4 my-2"><i class="fas fa-edit fa-3x"></i></button>\n' +
                '            <button data-toggle="tooltip" title="Contacts" data-placement="top" onclick="ThreadManager.load().contacts()" class="btn btn-outline-info btn-circle btn-circle-xl mx-4 my-2"><i class="far fa-address-book fa-3x"></i></button>\n' +
                '            <button data-toggle="tooltip" title="Messenger Settings" data-placement="top" onclick="ThreadManager.load().settings()" class="btn btn-outline-dark btn-circle btn-circle-xl mx-4 my-2"><i class="fas fa-cog fa-3x"></i></button>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '</div>'
        },
        global_settings : function(data){
            return '<div class="form-row">\n' +
                '<div class="col-6"><label class="control-label d-block h5 font-weight-bold" for="online_status_switch">Online Status</label>\n' +
                '<div id="online_status_switch" class="btn-group btn-group-toggle" data-toggle="buttons">\n' +
                '<label data-toggle="tooltip" title="Online" data-placement="left" class="pointer_area btn btn-success '+(data.online_status === 1 ? 'active glowing_btn' : '')+'">\n' +
                '<input type="radio" name="online_status" value="1" autocomplete="off" '+(data.online_status === 1 ? 'checked' : '')+'><i class="fas fa-wifi"></i>\n' +
                '</label>\n' +
                '<label data-toggle="tooltip" title="Away" data-placement="bottom" class="pointer_area btn btn-danger '+(data.online_status === 2 ? 'active glowing_btn' : '')+'">\n' +
                '<input type="radio" name="online_status" value="2" autocomplete="off" '+(data.online_status === 2 ? 'checked' : '')+'><i class="fas fa-user-slash"></i>\n' +
                '</label>\n' +
                '<label data-toggle="tooltip" title="Offline" data-placement="right" class="pointer_area btn btn-dark '+(data.online_status === 0 ? 'active glowing_btn' : '')+'">\n' +
                '<input type="radio" name="online_status" value="0" autocomplete="off" '+(data.online_status === 0 ? 'checked' : '')+'><i class="fas fa-power-off"></i>\n' +
                '</label>\n' +
                '</div></div>\n' +
                '<div class="col-6 mt-1 text-right">' +
                '    <div class="btn-group-vertical mr-1">' +
                '        <button data-toggle="tooltip" data-placement="left" title="Upload Avatar" onclick="$(\'#messenger_avatar_upload\').click()" class="btn btn-sm btn-outline-success"><i class="fas fa-cloud-upload-alt"></i></button>'+
                '        <button data-toggle="tooltip" data-placement="left" title="Remove Avatar" onclick="ThreadManager.archive().Avatar()" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>' +
                '    </div>'+
                '    <div data-toggle="tooltip" title="Upload avatar" data-placement="right" onclick="$(\'#messenger_avatar_upload\').click()" class="pointer_area d-inline">\n' +
                '         <img alt="Avatar" height="62" width="62" class="rounded avatar-is-'+(data.online_status === 1 ? "online" : data.online_status === 2 ? "away" : "offline")+'" src="'+TippinManager.common().slug+'"/>\n' +
                '    </div>\n' +
                '</div>' +
                '</div><hr>'+
                '<table class="table mb-0 table-sm table-hover"><tbody>\n' +
                '<tr class="'+(data.message_popups ? 'bg-light' : '')+'">\n' +
                '<td class="pointer_area" onclick="$(\'#message_popups\').click()"><div class="h4 mt-1"><i class="fas fa-caret-right"></i> <span class="h5">Message Popups</span></div></td>\n' +
                '<td><div class="mt-1 float-right"><span class="switch switch-sm mt-1"><input class="switch switch_input m_setting_toggle" id="message_popups" name="message_popups" type="checkbox" '+(data.message_popups ? 'checked' : '')+'/><label for="message_popups"></label></span></div></td>\n' +
                '</tr>\n' +
                '<tr class="'+(data.message_sound ? 'bg-light' : '')+'">\n' +
                '<td class="pointer_area" onclick="$(\'#message_sounds\').click()"><div class="h4 mt-1"><i class="fas fa-caret-right"></i> <span class="h5">Message Sounds</span></div></td>\n' +
                '<td><div class="mt-1 float-right"><span class="switch switch-sm mt-1"><input class="switch switch_input m_setting_toggle" id="message_sounds" name="message_sounds" type="checkbox" '+(data.message_sound ? 'checked' : '')+'/><label for="message_sounds"></label></span></div></td>\n' +
                '</tr>\n' +
                '<tr class="'+(data.call_ringtone_sound ? 'bg-light' : '')+'">\n' +
                '<td class="pointer_area" onclick="$(\'#call_ringtone_sound\').click()"><div class="h4 mt-1"><i class="fas fa-caret-right"></i> <span class="h5">Call Ringtone</span></div></td>\n' +
                '<td><div class="mt-1 float-right"><span class="switch switch-sm mt-1"><input class="switch switch_input m_setting_toggle" id="call_ringtone_sound" name="call_ringtone_sound" type="checkbox" '+(data.call_ringtone_sound ? 'checked' : '')+'/><label for="call_ringtone_sound"></label></span></div></td>\n' +
                '</tr>\n' +
                '<tr class="'+(data.knoks ? 'bg-light' : '')+'">\n' +
                '<td class="pointer_area" onclick="$(\'#allow_knoks\').click()"><div class="h4 mt-1"><i class="fas fa-caret-right"></i> <span class="h5">Receive Knocks</span></div></td>\n' +
                '<td><div class="mt-1 float-right"><span class="switch switch-sm mt-1"><input class="switch switch_input m_setting_toggle" id="allow_knoks" name="allow_knoks" type="checkbox" '+(data.knoks ? 'checked' : '')+'/><label for="allow_knoks"></label></span></div></td>\n' +
                '</tr>\n' +
                '<tr class="'+(data.calls_outside_networks ? 'bg-light' : '')+'">\n' +
                '<td class="pointer_area" onclick="$(\'#allow_all_calls\').click()"><div class="h4 mt-1"><i class="fas fa-caret-right"></i> <span class="h5">Receive calls from non-friends</span></div></td>\n' +
                '<td><div class="mt-1 float-right"><span class="switch switch-sm mt-1"><input class="switch switch_input m_setting_toggle" id="allow_all_calls" name="allow_all_calls" type="checkbox" '+(data.calls_outside_networks ? 'checked' : '')+'/><label for="allow_all_calls"></label></span></div></td>\n' +
                '</tr>\n' +
                '</tbody></table>\n'
        },
        send_msg_btn : function(emoji){
            if(emoji){
                return '<div id="inline_send_msg_btn" class="float-right mr-1 inline_send_msg_btn_1"><a title="Click to send or press enter" href="#" ' +
                    'onclick="'+(ThreadManager.state().type === 3 ? 'ThreadManager.newForms().newPrivate(0);' : 'ThreadManager.send();')+' return false;" class="text-success h3"><i class="fas fa-comment-dots"></i></a></div>'
            }
            return '<div id="inline_send_msg_btn" class="float-right inline_send_msg_btn_2"><a title="Click to send or press enter" href="#" ' +
                'onclick="'+(ThreadManager.state().type === 3 ? 'ThreadManager.newForms().newPrivate(0);' : 'ThreadManager.send();')+' return false;" class="text-success h3"><i class="fas fa-comment-dots"></i></a></div>'
        },
        socket_error : function(margin){
            if(margin){
                return '<div class="my-2 alert alert-danger text-danger text-center"><span class="spinner-border spinner-border-sm"></span> Connection error, messages may be delayed</div>';
            }
            return '<div class="d-inline alert alert-danger shadow-sm mr-2 text-danger text-center pt-2"><span class="spinner-border spinner-border-sm"></span> Connection error</div>'
        },
        seen_by : function(data){
            return '<div id="seen-by_'+data.message_id+'" class="seen-by pb-1 w-100"></div>'
        },
        bobble_head : function(data, bottom){
            return '<div class="bobble-head-item d-inline bobble_head_'+data.owner_id+'"><img class="rounded-circle bobble-image '+(bottom && (!data.in_chat || [0,2].includes(data.online)) ? "bobble-image-away" : "")+'" src="'+data.avatar+'" ' +
                'title="'+(data.typing || bottom && data.online === 1 && data.in_chat ? TippinManager.format().escapeHtml(data.name) : "Seen by "+TippinManager.format().escapeHtml(data.name))+'" />' +
                '<div class="d-inline bobble-typing">'+(data.typing ? templates.typing_elipsis(data.owner_id) : '')+'</div></div>';
        },
        typing_elipsis : function(id){
            return '<div id="typing_'+id+'" class="typing-ellipsis"><div><i class="fas fa-circle"></i></div><div><i class="fas fa-circle"></i></div><div><i class="fas fa-circle"></i></div></div>'
        },
        loader : function(){
            return '<div class="col-12 mt-5 text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></div>'
        },
        thread_body : function(data){
            switch(data.recent_message.message_type){
                case 1:
                    return '<em>'+data.recent_message.name+'</em> : <i class="far fa-image"></i> Sent an image';
                case 2:
                    return '<em>'+data.recent_message.name+'</em> : <i class="fas fa-cloud-download-alt"></i> Sent a file';
                default:
                    return '<em>'+data.recent_message.name+'</em> : ' + (typeof emojione !== 'undefined' ? emojione.toImage(data.recent_message.body) : data.recent_message.body)
            }
        },
        thread_highlight : function(data){
            if(data.options.lockout){
                return 'alert-danger'
            }
            if(data.call.status){
                if(data.call.in_call){
                    return 'alert-success'
                }
                return 'alert-dark'
            }
            if(data.unread && data.unread_count > 0){
                return 'alert-info'
            }
            return ''
        },
        thread_status : function(data){
            if(data.call.status){
                let details = 'Call <i class="fas fa-video"></i>';
                if(data.call.in_call){
                    return ' <span class="shadow-sm badge badge-pill badge-danger">'+details+'</span>'
                }
                if(data.call.left_call){
                    return ' <span class="shadow-sm badge badge-pill badge-success">'+details+'</span>'
                }
                return ' <span class="shadow-sm badge badge-pill badge-warning">'+details+'</span>'
            }
            if(data.options.lockout){
                return ' <span class="shadow-sm badge badge-pill badge-danger">Locked <i class="fas fa-lock"></i></span>'
            }
            if(data.unread && data.unread_count > 0){
                return ' <span class="shadow-sm badge badge-pill badge-primary">'+data.unread_count+' <i class="fas fa-comment-dots"></i></span>'
            }
            return ''
        },
        thread_logs : function(data){
            let html = '<div class="col-12">';
            data.forEach(function (message) {
                html += '<div class="text-right"><span class="badge badge-pill badge-light">'+(new Date(message.created_at).toDateString())+'</span></div>';
                html += templates.system_message(message)
            });
            html += '</div>';
            return html
        },
        messenger_search_friend : function(profile){
            switch(profile.network){
                case 0: return '';
                case 1: return ' <span class="shadow-sm badge badge-pill badge-success"><i class="fas fa-user"></i> Friend</span>';
                case 2: return ' <span class="shadow-sm badge badge-pill badge-info"><i class="fas fa-user-plus"></i> Sent request</span>';
                case 3: return ' <span class="shadow-sm badge badge-pill badge-primary"><i class="fas fa-user-friends"></i> Pending request</span>';
            }
            return ''
        },
        messenger_search : function(profile){
            let type = '<span class="badge badge-light"><i class="fas fa-restroom"></i> User</span>';
            if(profile.alias === 'company') type = '<span class="badge badge-light"><i class="fas fa-building"></i> Company</span>';
            return '<li title="'+TippinManager.format().escapeHtml(profile.name)+'" class="thread_list_item">' +
                '<a onclick="ThreadManager.load().createPrivate({slug : \''+profile.slug+'\', type : \''+profile.alias+'\'}); return false;" href="#">' +
                '<div class="media"><div class="media-left media-middle"><img src="'+profile.avatar+'" class="media-object rounded-circle thread-list-avatar avatar-is-'+(profile.online === 1 ? "online" : profile.online === 2 ? "away" : "offline")+'" /></div>' +
                '<div class="media-body thread_body_li"><div class="header d-inline"><small><div class="d-none d-sm-block float-right">'+type+'</div></small>' +
                '<div class="from h5 font-weight-bold">'+profile.name+'</div></div><div class="description mt-n2">' +
                templates.messenger_search_friend(profile) +
                '</div></div></div></a></li>'
        },
        private_thread : function(data, selected){
            return '<li title="'+TippinManager.format().escapeHtml(data.name)+'" id="thread_list_'+data.thread_id+'" class="thread_list_item '+(selected ? "alert-warning shadow-sm rounded" : "")+' '+templates.thread_highlight(data)+'">' +
                '<div class="thread-list-status">'+templates.thread_status(data)+'</div> '+
                '<a onclick="ThreadManager.load().initiate_thread({thread_id : \''+data.thread_id+'\'}); return false;" href="/messenger/'+data.thread_id+'">' +
                '<div class="media"><div class="media-left media-middle"><img src="'+data.avatar+'" class="media-object rounded-circle thread-list-avatar avatar-is-'+(data.online === 1 ? "online" : data.online === 2 ? "away" : "offline")+'" /></div>' +
                '<div class="media-body thread_body_li"><div class="header d-inline"><small><div class="d-none d-sm-block float-right date"><time class="timeago" datetime="'+data.updated_at+'">'+TippinManager.format().makeTimeAgo(data.updated_at)+'</time></div></small>' +
                '<div class="from">'+data.name+'</div></div><div class="description">' +
                templates.thread_body(data) +
                '</div></div></div></a></li>'
        },
        group_thread : function(data, selected){
            return '<li title="'+TippinManager.format().escapeHtml(data.name)+'" id="thread_list_'+data.thread_id+'" class="thread_list_item '+(selected ? "alert-warning shadow-sm rounded" : "")+' '+templates.thread_highlight(data)+'">' +
                '<div class="thread-list-status">'+templates.thread_status(data)+'</div> '+
                '<a onclick="ThreadManager.load().initiate_thread({thread_id : \''+data.thread_id+'\'}); return false;" href="/messenger/'+data.thread_id+'">' +
                '<div class="media"><div class="media-left media-middle"><img src="'+data.avatar+'/thumb" class="show_group_avatar_'+data.thread_id+' media-object rounded-circle thread-list-avatar thread-group-avatar '+(selected ? "avatar-is-online" : "avatar-is-offline")+'" /></div>' +
                '<div class="media-body thread_body_li"><div class="header d-inline"><small><div class="d-none d-sm-block float-right date"><time class="timeago" datetime="'+data.updated_at+'">'+TippinManager.format().makeTimeAgo(data.updated_at)+'</time></div></small>' +
                '<div class="from"><span class="font-weight-bold">'+data.name+'</span></div></div><div class="description">' +
                templates.thread_body(data) +
                '</div></div></div></a></li>'
        },
        message_body : function(data, pending){
            if(pending){
                switch(data.message_type){
                    case 1:
                    case 2:
                        return '<div class="h3 spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">\n' +
                                '  <span class="sr-only">Uploading...</span>\n' +
                                '</div>';
                    default:
                        return (typeof emojione !== 'undefined' ? emojione.toImage(data.body) : data.body)
                }
            }
            switch(data.message_type){
                case 1:
                    return '<a target="_blank" href="/images/messenger/'+data.message_id+'">' +
                               '<img class="msg_image NS img-fluid" src="/images/messenger/'+data.message_id+'/thumb" />' +
                               '<div class="h3 spinner-border text-secondary" style="width: 4rem; height: 4rem;" role="status"><span class="sr-only">loading...</span></div>'+
                           '</a>';
                case 2:
                    return '<i class="fas fa-cloud-download-alt"></i> Download<br/> <a href="/download/messenger/'+data.message_id+'" target="_blank">'+data.body+'</a>';
                default:
                    return (typeof emojione !== 'undefined' ? methods.makeLinks(methods.makeYoutube(emojione.toImage(data.body))) : methods.makeLinks(methods.makeYoutube(data.body)))
            }
        },
        message_archive : function(data, grouped){
            if(!ThreadManager.state().thread_lockout && ThreadManager.state().thread_admin){
                return '<div onclick="ThreadManager.archive().Message({id : \''+data.message_id+'\'})" class="message_hover_opt message_hover_group_opt float-left ml-1 pt-'+(grouped ? '1' : '2')+' h6 text-secondary pointer_area NS"><i title="Delete Message" class="fas fa-trash"></i></div>'
            }
            return '';
        },
        my_message_archive : function(data, grouped){
            if(!ThreadManager.state().thread_lockout){
                return '<div onclick="ThreadManager.archive().Message({id : \''+data.message_id+'\'})" class="message_hover_opt float-right mr-1 pt-'+(grouped ? '1' : '2')+' h6 text-secondary pointer_area NS"><i title="Delete Message" class="fas fa-trash"></i></div>'
            }
            return '';
        },
        pending_message : function(data){
            return '<div id="pending_message_'+data.message_id+'" class="message my-message"><div class="message-body"><div class="message-body-inner"><div class="message-info">' +
                '<h5> <i class="far fa-clock"></i>a few seconds ago</h5></div><hr><div class="message-text">' +
                templates.message_body(data, true) +
                '</div></div></div>' +
                '<div id="pending_message_loading_'+data.message_id+'" class="float-right mr-1 pt-2 h6 text-primary NS"><span title="Sending..." class="spinner-border spinner-border-sm"></span></div>' +
                '<div class="clearfix"></div></div>'
        },
        pending_message_grouped : function(data){
            return '<div id="pending_message_'+data.message_id+'" class="message grouped-message my-message"><div class="message-body"><div class="message-body-inner">' +
                '<div class="message-text pt-2">' +
                templates.message_body(data, true) +
                '</div></div></div>' +
                '<div id="pending_message_loading_'+data.message_id+'" class="float-right mr-1 pt-1 h6 text-primary NS"><span title="Sending..." class="spinner-border spinner-border-sm"></span></div>' +
                '<div class="clearfix"></div></div>'
        },
        my_message : function(data){
            return '<div id="message_'+data.message_id+'" class="message my-message"><div class="message-body"><div class="message-body-inner"><div class="message-info">' +
                '<h5> <i class="far fa-clock"></i><time title="'+TippinManager.format().makeUtcLocal(data.created_at)+'" class="timeago" datetime="'+data.created_at+'">'+TippinManager.format().makeTimeAgo(data.created_at)+'</time></h5></div><hr><div class="message-text">' +
                templates.message_body(data, false) +
                '</div></div></div>'+templates.my_message_archive(data, false)+'<div class="clearfix"></div></div>'
        },
        my_message_grouped : function(data){
            return '<div id="message_'+data.message_id+'" class="message grouped-message my-message"><div class="message-body"><div class="message-body-inner">' +
                '<div title="'+TippinManager.format().escapeHtml(data.name)+' on '+TippinManager.format().makeUtcLocal(data.created_at)+'" class="message-text pt-2">' +
                templates.message_body(data, false) +
                '</div></div></div>'+templates.my_message_archive(data, true)+'<div class="clearfix"></div></div>'
        },
        message : function(data){
            return '<div id="message_'+data.message_id+'" class="message info"><a href="'+data.slug+'" target="_blank"><img title="'+TippinManager.format().escapeHtml(data.name)+'" class="rounded-circle message-avatar" src="'+data.avatar+'"></a>' +
                '<div class="message-body"><div class="message-body-inner"><div class="message-info">' +
                '<h4>'+data.name+'</h4><h5> <i class="far fa-clock"></i><time title="'+TippinManager.format().makeUtcLocal(data.created_at)+'" class="timeago" datetime="'+data.created_at+'">'+TippinManager.format().makeTimeAgo(data.created_at)+'</time></h5></div><hr><div class="message-text">' +
                templates.message_body(data, false) +
                '</div></div></div>' +templates.message_archive(data, false)+ '<div class="clearfix"></div></div>'
        },
        message_grouped : function(data){
            return '<div id="message_'+data.message_id+'" class="message grouped-message info"><div class="message-body"><div class="message-body-inner">' +
                '<div title="'+TippinManager.format().escapeHtml(data.name)+' on '+TippinManager.format().makeUtcLocal(data.created_at)+'" class="message-text pt-2">' +
                templates.message_body(data, false) +
                '</div></div></div> '+templates.message_archive(data, true)+' <div class="clearfix"></div></div>'
        },
        loading_history : function(){
            return '<div id="loading_history_marker" class="system-message pt-0 mt-n4 w-100 text-center"> ' +
                '<span class="text-primary spinner-border spinner-border-sm"></span></div>';
        },
        end_of_history : function(){
            return '<div id="end_history_marker" class="alert-dark shadow-sm rounded mb-4 mt-n3 w-100 text-center"> ' +
                '<strong><i class="fas fa-comment-slash"></i> End of conversation</div>';
        },
        system_message : function (data) {
            let icon = 'fas fa-info-circle';
            switch(data.message_type){
                case 90:
                    icon = 'fas fa-video';
                break;
                case 91:
                    icon = 'far fa-image';
                break;
                case 92:
                    icon = 'fas fa-trash';
                break;
                case 93:
                    icon = 'fas fa-folder-plus';
                break;
                case 94:
                    icon = 'fas fa-edit';
                break;
                case 95:
                    icon = 'fas fa-user-shield';
                break;
                case 96:
                    icon = 'fas fa-chess-queen';
                break;
                case 97:
                    icon = 'fas fa-sign-out-alt';
                break;
                case 98:
                    icon = 'far fa-minus-square';
                break;
                case 88:
                case 99:
                    icon = 'far fa-plus-square';
                break;
            }
            return '<div id="message_'+data.message_id+'" class="system-message alert-warning rounded py-1 w-100 text-center" ' +
                    'title="'+TippinManager.format().escapeHtml(data.name)+' on '+TippinManager.format().makeUtcLocal(data.created_at)+'"><i class="'+icon+'"></i> ' +
                    '<strong>'+data.name+'</strong> '+TippinManager.format().escapeHtml(data.body)+'</div>';
        },
        thread_call_state : function(data){
            if(data.options.lockout) return '';
            if(data.call.status){
                if(data.call.in_call){
                    return '<button onclick="CallManager.join({call_id : \''+data.call.call_id+'\', thread_id : \''+data.thread_id+'\', call_type : 1}, true)"' +
                        ' data-toggle="tooltip" title="You are in this call" data-placement="left" class="btn btn-lg btn-outline-danger video_btn pt-1 pb-0 px-2" type="button"><i class="fas fa-video fa-2x"></i></button>'
                }
                return '<button onclick="CallManager.join({call_id : \''+data.call.call_id+'\', thread_id : \''+data.thread_id+'\', call_type : 1}, true)" ' +
                    'data-toggle="tooltip" title="Join call" data-placement="left" class="glowing_btn btn btn-lg btn-success video_btn pt-1 pb-0 px-2" type="button"><i class="fas fa-video fa-2x"></i></button>'
            }
            if(data.thread_type === 1){
                return '<button onclick="ThreadManager.calls().initCall()" data-toggle="tooltip" title="Call '+TippinManager.format().escapeHtml(data.name)+'" data-placement="left" ' +
                    'class="btn btn-lg text-secondary btn-light pt-1 pb-0 px-2 video_btn" type="button"><i class="fas fa-video fa-2x"></i></button>'
            }
            if(data.options.admin || data.options.admin_call){
                return '<button onclick="ThreadManager.calls().initCall()" data-toggle="tooltip" title="Create group call" data-placement="left" ' +
                        'class="btn btn-lg text-secondary btn-light pt-1 pb-0 px-2 video_btn" type="button"><i class="fas fa-video fa-2x"></i></button>'
            }
            return ''
        },
        thread_group_header : function(data){
            let knok = '<button onclick="ThreadManager.calls().sendKnock()" id="knok_btn" data-toggle="tooltip" title="Knock at '+TippinManager.format().escapeHtml(data.name)+'" ' +
                'data-placement="bottom" class="btn btn-lg text-secondary btn-light pt-1 pb-0 px-2" type="button"><i class="fas fa-hand-rock fa-2x"></i></button>',
            admin = '<a class="dropdown-item" onclick="ThreadManager.group().viewSettings(); return false;" id="threadOptionLink" href="#"><i class="fas fa-cog"></i> Group Settings</a>\n'+
                    '<a class="dropdown-item" onclick="ThreadManager.group().viewInviteGenerator(); return false;" id="threadOptionLink" href="#"><i class="fas fa-link"></i> Generate Invite Link</a>\n';
            return '<div id="thread_header_area"><div class="dropdown float-right">\n' +
                    '<span id="thread_info_area">' +
                    '<span id="thread_option_call">'+templates.thread_call_state(data)+'</span>\n' +
                    (!data.options.lockout && data.options.admin ? knok : '')+
                    '</span><span id="thread_error_area"></span>'+
                    '    <button class="btn btn-lg text-secondary btn-light dropdown-toggle pt-1 pb-0 px-2" type="button" data-toggle="dropdown"><i class="fas fa-cog fa-2x"></i></button>\n' +
                    '    <div class="dropdown-menu dropdown-menu-right">\n' +
                    '        <div class="dropdown-header py-0 h6"><img id="group_avatar_'+data.thread_id+'" alt="Group Image" class="show_group_avatar_'+data.thread_id+' rounded-circle small_img" src="'+data.avatar+'/thumb"/>' +
                    '           <span id="group_name_area">'+data.name+'</span></div>\n' +
                    '        <div class="dropdown-divider"></div>\n' +
                    '        <a onclick="ThreadManager.load().threadLogs(); return false;" class="dropdown-item" href="#"><i class="fas fa-database"></i> View Logs</a>\n' +
                    (!data.options.lockout && (data.options.add_participants || data.options.admin) ? '<a class="dropdown-item" onclick="ThreadManager.group().addParticipants(); return false;" id="addParticipantLink" href="#"><i class="fas fa-user-plus"></i> Add participants</a>' : '')+
                    '        <a class="dropdown-item" onclick="ThreadManager.group().viewParticipants(); return false;" id="viewParticipantLink" href="#"><i class="fas fa-users"></i> '+(data.options.admin ? 'Manage' : 'View')+' participants</a>\n' +
                    (!data.options.lockout && data.options.admin ? admin : '')+
                    '<div class="dropdown-divider"></div><a class="dropdown-item" onclick="ThreadManager.group().leaveGroup(); return false;" id="leaveGroupLink" href="#"><i class="fas fa-sign-out-alt"></i> Leave Group</a>'+
                    '</div>'+
                '<button onclick="ThreadManager.load().closeOpened()" title="Close" class="btn btn-lg text-danger btn-light pt-1 pb-0 px-2 mr-1" type="button"><i class="fas fa-times fa-2x"></i></button>'+
                '</div>'+
                '<img onclick="ThreadTemplates.render().show_thread_avatar(\''+data.avatar+'\')" data-toggle="tooltip" data-placement="right" title="'+TippinManager.format().escapeHtml(data.name)+'" class="ml-1 rounded-circle medium-image main-bobble-online pointer_area" src="'+data.avatar+'/thumb" />'+
                '<div id="thread_error_area"></div></div>'
        },
        thread_network_opt : function(data){
            switch(data.network){
                case 0: return '<a class="dropdown-item network_option" onclick="NetworksManager.action({dropdown : true, owner_id : \''+data.owner_id+'\', action : \'add\', slug : \''+data.slug+'\', type : \''+data.type+'\'}); return false;" href="#"><i class="fas fa-user-plus"></i> Add friend</a>';
                case 1: return '<a class="dropdown-item network_option" onclick="NetworksManager.action({dropdown : true, owner_id : \''+data.owner_id+'\', action : \'remove\', slug : \''+data.slug+'\', type : \''+data.type+'\'}); return false;" href="#"><i class="fas fa-user-times"></i> Remove friend</a>';
                case 2: return '<a class="dropdown-item network_option" onclick="NetworksManager.action({dropdown : true, owner_id : \''+data.owner_id+'\', action : \'cancel\', slug : \''+data.slug+'\', type : \''+data.type+'\'}); return false;" href="#"><i class="fas fa-ban"></i> Cancel friend request</a>';
                case 3: return '<a class="dropdown-item network_option" onclick="NetworksManager.action({dropdown : true, owner_id : \''+data.owner_id+'\', action : \'accept\', slug : \''+data.slug+'\', type : \''+data.type+'\'}); return false;" href="#"><i class="far fa-check-circle"></i> Accept friend request</a>' +
                    '<a class="dropdown-item network_option" onclick="NetworksManager.action({dropdown : true, owner_id : \''+data.owner_id+'\', action : \'deny\', slug : \''+data.slug+'\', type : \''+data.type+'\'}); return false;" href="#"><i class="fas fa-ban"></i> Deny friend request</a>';
            }
            return ''
        },
        thread_private_header : function(data, party){
            return '<div id="thread_header_area"><div class="dropdown float-right">\n' +
                '<span id="thread_info_area">' +
                '    <span id="thread_option_call">'+(!party.can_call && !data.call.status ? '' : templates.thread_call_state(data))+'</span>\n' +
                (!data.options.lockout && party.knoks ?
                    '<button onclick="ThreadManager.calls().sendKnock()" id="knok_btn" data-toggle="tooltip" title="Knock at '+TippinManager.format().escapeHtml(data.name)+'" data-placement="bottom" class="btn btn-lg text-secondary btn-light pt-1 pb-0 px-2 mr-1" type="button"><i class="fas fa-hand-rock fa-2x"></i></button>'
                    : '')+
                '</span><span id="thread_error_area"></span>'+

                '<button class="btn btn-lg text-secondary btn-light dropdown-toggle pt-1 pb-0 px-2" type="button" data-toggle="dropdown"><i class="fas fa-cog fa-2x"></i></button>\n' +
                '<div class="dropdown-menu dropdown-menu-right">\n' +
                '    <div onclick="window.open(\''+party.route+'\')" class="pointer_area dropdown-header py-0 h6"><img alt="Profile Image" class="rounded-circle small_img" src="'+data.avatar+'"/> '+data.name+'</div>\n' +
                '    <div class="dropdown-divider"></div>\n' +
                '    <a onclick="ThreadManager.load().threadLogs(); return false;" class="dropdown-item" href="#"><i class="fas fa-database"></i> View Logs</a>\n' +
                '    <div id="network_for_'+party.owner_id+'" class="profile_network_options">'+templates.thread_network_opt(party)+'</div>'+
                '<div class="dropdown-divider"></div><a onclick="ThreadManager.archive().Thread(); return false;" class="dropdown-item" href="#"><i class="fas fa-trash"></i> Delete Conversation</a></div>'+
                '<button onclick="ThreadManager.load().closeOpened()" title="Close" class="btn btn-lg text-danger btn-light pt-1 pb-0 px-2 mr-1" type="button"><i class="fas fa-times fa-2x"></i></button>'+
                '</div><div id="main_bobble_'+party.owner_id+'">'+templates.thread_private_header_bobble(data)+'</div></div>'
        },
        thread_new_header : function(party){
            return '<div id="thread_header_area"><div class="dropdown float-right">\n' +
                '<span id="thread_info_area">' +
                '<span id="thread_option_call"></span>' +
                '</span><span id="thread_error_area"></span>'+
                '<button class="btn btn-lg text-secondary btn-light dropdown-toggle pt-1 pb-0 px-2" type="button" data-toggle="dropdown"><i class="fas fa-cog fa-2x"></i></button>\n' +
                '<div class="dropdown-menu dropdown-menu-right">\n' +
                '    <div onclick="window.open(\''+party.route+'\')" class="pointer_area dropdown-header py-0 h6"><img alt="Profile Image" class="rounded-circle small_img" src="'+party.avatar+'"/> '+party.name+'</div>\n' +
                '    <div class="dropdown-divider"></div>\n' +
                '    <div id="network_for_'+party.owner_id+'" class="profile_network_options">'+templates.thread_network_opt(party)+'</div>'+
                '</div>'+
                '<button onclick="ThreadManager.load().closeOpened()" title="Close" class="btn btn-lg text-danger btn-light pt-1 pb-0 px-2 mr-1" type="button"><i class="fas fa-times fa-2x"></i></button>'+
                '</div><div id="main_bobble_'+party.owner_id+'">'+templates.thread_private_header_bobble(party)+'</div>'+
                '</div>'
        },
        thread_new_fill : function(data){
            return '<div class="col-12 text-center text-info font-weight-bold h4 mt-5">\n' +
                '<i class="fas fa-comments"></i> Starting a new conversation with<br/> '+data.name+
                '</div>'
        },
        thread_empty_search : function(more, none){
            let msg = 'Search above for other profiles in Tipz Messenger!';
            if(more) msg = none ? 'No matching profiles were found' : 'Use at least 3 characters in your query';
            return '<div class="col-12 text-center text-info font-weight-bold h4 mt-5">\n' +
                '<i class="fas fa-search"></i> '+msg+
                '</div>'
        },
        thread_private_header_bobble : function(data){
            let status;
            switch(data.online){
                case 0:
                    if(data.last_active){
                        status = 'last seen '+TippinManager.format().makeTimeAgo(data.last_active);
                    }
                    else{
                        status = 'is offline';
                    }
                    break;
                case 1: status = 'is online'; break;
                case 2: status = 'is away'; break;
            }
            return '<img onclick="ThreadTemplates.render().show_thread_avatar(\''+data.avatar+'\')" data-toggle="tooltip" data-placement="right" ' +
                'title="'+TippinManager.format().escapeHtml(data.name)+' '+status+'" class="ml-1 rounded-circle medium-image main-bobble-'+(data.online === 1 ? "online" : (data.online === 2 ? "away" : "offline"))+' pointer_area" src="'+data.avatar+'" />'
        },
        my_avatar_status : function(state){
            let online;
            switch(state){
                case 0: online = 'offline'; break;
                case 1: online = 'online'; break;
                case 2: online = 'away'; break;
            }
            return '<img data-toggle="tooltip" data-placement="right" title="You are '+online+'" class="my-global-avatar ml-1 rounded-circle medium-image avatar-is-'+online+'" src="'+TippinManager.common().slug+'" />'
        },
        show_thread_avatar : function(avatar){
            TippinManager.alert().showAvatar(ThreadManager.state().t_name, avatar+'/full');
        },
        thread_new_message_alert : function(){
            return '<div class="text-center h6 font-weight-bold"><div class="py-2 alert alert-primary border-info shadow" role="alert">You have new messages <i class="fas fa-level-down-alt"></i></div></div>'
        },
        group_settings : function(settings){
            return '<form id="group_settings_form" action="javascript:ThreadManager.group().saveSettings()">\n' +
                '    <div class="form-group">\n' +
                '        <div data-toggle="tooltip" title="Edit Avatar" data-placement="right" onclick="ThreadManager.group().groupAvatar(\''+settings.avatar+'\')" class="pointer_area d-inline">\n' +
                '            <img alt="Group Image" height="50" class="rounded mr-2" src="'+settings.avatar+'"/><i class="fas fa-edit"></i>\n' +
                '        </div>\n' +
                '        <div class="float-right">\n' +
                '            <div data-toggle="tooltip" title="Participants" data-placement="left" class="h2 pointer_area mt-2" onclick="ThreadManager.group().viewParticipants()">\n' +
                '                <span class="badge badge-pill badge-primary"><i class="fas fa-users"></i> '+settings.participant_count+'</span>\n' +
                '            </div>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '    <hr>\n' +
                '    <div class="mx-n2 form-group input-group-lg rounded bg-gradient-light text-dark pb-3 pt-2 px-3 shadow-sm">\n' +
                '        <label class="font-weight-bold h5 control-label" for="g_s_group_subject">Group Name:</label>\n' +
                '        <input maxlength="50" class="form-control font-weight-bold shadow-sm" id="g_s_group_subject" placeholder="Name the group conversation" name="subject" required value="'+settings.name+'">\n' +
                '    </div>\n' +
                '    <hr>\n' +
                '    <div class="form-row mx-n2 rounded bg-gradient-light text-dark pb-3 pt-2 px-3 shadow-sm">\n' +
                '        <label class="font-weight-bold h5 control-label" for="g_s_table">Non-Admin Permissions:</label>\n' +
                '        <table id="g_s_table" class="table mb-0 table-sm table-hover">\n' +
                '            <tbody>\n' +
                '            <tr class="'+(settings.add_participants ? 'alert-primary' : '')+'">\n' +
                '                <td class="pointer_area" onclick="$(\'#g_s_add_participants\').click()">\n' +
                '                    <div class="h4 mt-1"><i class="fas fa-caret-right"></i> <span class="h5">Add Participants</span></div>\n' +
                '                </td>\n' +
                '                <td>\n' +
                '                    <div class="mt-1 float-right"><span class="switch switch-sm mt-1">\n' +
                '                        <input class="switch switch_input m_setting_toggle" id="g_s_add_participants" name="g_s_add_participants" type="checkbox" '+(settings.add_participants ? 'checked' : '')+'>\n' +
                '                        <label for="g_s_add_participants"></label>\n' +
                '                    </span></div>\n' +
                '                </td>\n' +
                '            </tr>\n' +
                '            <tr class="'+(settings.start_calls ? 'alert-primary' : '')+'">\n' +
                '                <td class="pointer_area" onclick="$(\'#g_s_admin_call\').click()">\n' +
                '                    <div class="h4 mt-1"><i class="fas fa-caret-right"></i> <span class="h5">Start calls</span></div>\n' +
                '                </td>\n' +
                '                <td>\n' +
                '                    <div class="mt-1 float-right"><span class="switch switch-sm mt-1">\n' +
                '                        <input class="switch switch_input m_setting_toggle" id="g_s_admin_call" name="g_s_admin_call" type="checkbox" '+(settings.start_calls ? 'checked' : '')+'>\n' +
                '                        <label for="g_s_admin_call"></label>\n' +
                '                    </span></div>\n' +
                '                </td>\n' +
                '            </tr>\n' +
                '            <tr class="'+(settings.send_messages ? 'alert-primary' : '')+'">\n' +
                '                <td class="pointer_area" onclick="$(\'#g_s_send_message\').click()">\n' +
                '                    <div class="h4 mt-1"><i class="fas fa-caret-right"></i> <span class="h5">Send messages</span></div>\n' +
                '                </td>\n' +
                '                <td>\n' +
                '                    <div class="mt-1 float-right"><span class="switch switch-sm mt-1">\n' +
                '                        <input class="switch switch_input m_setting_toggle" id="g_s_send_message" name="g_s_send_message" type="checkbox" '+(settings.send_messages ? 'checked' : '')+'>\n' +
                '                        <label for="g_s_send_message"></label>\n' +
                '                    </span></div>\n' +
                '                </td>\n' +
                '            </tr>\n' +
                '            </tbody>\n' +
                '        </table>\n' +
                '    </div>\n' +
                '    <hr>\n' +
                '    <div class="text-center form-group mb-0 py-2 alert-danger shadow rounded">\n' +
                '        <div class="mb-1 font-weight-bold">You will be asked to confirm this action</div>\n' +
                '        <button onclick="ThreadManager.archive().Thread()" type="button" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Remove Group</button>\n' +
                '    </div>\n' +
                '</form>'
        },
        group_avatar : function(img){
            return '<div class="row">\n' +
                '    <div class="text-center mx-auto">\n' +
                '        <img alt="Group Image" class="rounded group-current-avatar" src="'+img+'"/>\n' +
                '    </div>\n' +
                '</div>\n' +
                '<div class="row mt-2">\n' +
                '    <div class="col-12 accordion_arrow text-dark" id="avatar_opts">\n' +
                '        <div class="card bg-gradient-light mb-1">\n' +
                '            <div class="card-header h4 pointer_area collapsed" data-toggle="collapse" data-target="#opt1">\n' +
                '                <i class="fas fa-ellipsis-v"></i> Choose a default group avatar\n' +
                '            </div>\n' +
                '            <div id="opt1" class="collapse" data-parent="#avatar_opts">\n' +
                '                <div class="card-body pb-2 h5 bg-light text-dark">\n' +
                '                    <div class="col-12">\n' +
                '                        <label class="control-label">Select default avatar:</label>\n' +
                '                        <div id="default_avatar" class="form-row">\n' +
                '                            <div class="row justify-content-center">' +
                '                            <div class="col-4 col-md-3 grp-img-box"><label><img src="/images/messenger/1.png" class="img-thumbnail grp-img-check grp-img-checked"><input type="radio" name="default_avatar" value="1" class="NS" autocomplete="off" checked></label></div>' +
                '                            <div class="col-4 col-md-3 grp-img-box"><label><img src="/images/messenger/2.png" class="img-thumbnail grp-img-check"><input type="radio" name="default_avatar" value="2" class="NS" autocomplete="off"></label></div>' +
                '                            <div class="col-4 col-md-3 grp-img-box"><label><img src="/images/messenger/3.png" class="img-thumbnail grp-img-check"><input type="radio" name="default_avatar" value="3" class="NS" autocomplete="off"></label></div>' +
                '                            </div>'+
                '                            <div class="row justify-content-center">' +
                '                            <div class="col-4 col-md-3 grp-img-box"><label><img src="/images/messenger/4.png" class="img-thumbnail grp-img-check"><input type="radio" name="default_avatar" value="4" class="NS" autocomplete="off"></label></div>' +
                '                            <div class="col-4 col-md-3 grp-img-box"><label><img src="/images/messenger/5.png" class="img-thumbnail grp-img-check"><input type="radio" name="default_avatar" value="5" class="NS" autocomplete="off"></label></div>' +
                '                            </div>'+
                '                        </div>\n' +
                '                    </div>\n' +
                '                    <div class="col-12 mt-2 text-center">\n' +
                '                        <button id="avatar_default_btn" onclick="ThreadManager.group().updateGroupAvatar({action : \'default\'})" type="button" class="btn btn-lg btn-success"><i class="far fa-save"></i> Save</button>\n' +
                '                    </div>\n' +
                '                </div>\n' +
                '            </div>\n' +
                '        </div>\n' +
                '        <div class="card bg-gradient-light mb-1">\n' +
                '            <div class="card-header h4 pointer_area collapsed" data-toggle="collapse" data-target="#opt2">\n' +
                '                <i class="fas fa-ellipsis-v"></i> Upload your own group avatar\n' +
                '            </div>\n' +
                '            <div id="opt2" class="collapse" data-parent="#avatar_opts">\n' +
                '                <div class="card-body h5 bg-light text-dark">\n' +
                '                    <h5 class="text-info">Select upload image to pick an avatar of your choosing:</h5>\n' +
                '                    <div class="text-center">\n' +
                '                        <button id="group_avatar_upload_btn" onclick="$(\'#avatar_image_file\').trigger(\'click\');" type="button" class="btn btn-lg btn-success"><i class="fas fa-cloud-upload-alt"></i> Upload Image</button>\n' +
                '                    </div>\n' +
                '                    <form class="NS" id="avatarUpload" enctype="multipart/form-data">\n' +
                '                        <input id="avatar_image_file" type="file" name="avatar_image_file" accept="image/*">\n' +
                '                    </form>\n' +
                '                </div>\n' +
                '            </div>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '</div>'
        },
        thread_generate_invite : function(back){
            return '<div id="grp_inv_make" class="row">\n' +
                '    <div class="col-12 mb-2">\n' +
                '        <div class="card">\n' +
                '            <div class="card-body bg-warning shadow-sm rounded">\n' +
                '                <h5>Generate a group invitation link that will allow those you share it with to join this group, given it meets the criteria you choose below</h5>\n' +
                '            </div>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '    <div class="col-12">\n' +
                '        <form id="grp_inv_form">\n' +
                '            <div class="form-row">\n' +
                '                <div class="form-group col-md-6">\n' +
                '                    <label for="grp_inv_expires">EXPIRE AFTER:</label>\n' +
                '                    <select class="form-control" id="grp_inv_expires" name="expires" required>\n' +
                '                        <option value="1">30 minutes</option>\n' +
                '                        <option value="2">1 hour</option>\n' +
                '                        <option value="3">6 hours</option>\n' +
                '                        <option value="4">12 hours</option>\n' +
                '                        <option value="5" selected>1 day</option>\n' +
                '                        <option value="6">Never</option>\n' +
                '                    </select>\n' +
                '                </div>\n' +
                '                <div class="form-group col-md-6">\n' +
                '                    <label for="grp_inv_uses">MAX NUMBER OF USES:</label>\n' +
                '                    <select class="form-control" id="grp_inv_uses" name="uses" required>\n' +
                '                        <option value="1" selected>No limit</option>\n' +
                '                        <option value="2">1 use</option>\n' +
                '                        <option value="3">5 uses</option>\n' +
                '                        <option value="4">10 uses</option>\n' +
                '                        <option value="5">25 uses</option>\n' +
                '                        <option value="6">50 uses</option>\n' +
                '                        <option value="7">100 uses</option>\n' +
                '                    </select>\n' +
                '                </div>\n' +
                '            </div>\n' +
                '            <div class="col-12">\n' +
                '                <div class="text-center">\n' +
                '                    <button type="button" id="grp_inv_back_btn" class="btn btn-outline-dark '+(back ? "" : "NS")+'"><i class="fas fa-undo"></i> Cancel</button>\n' +
                '                    <button type="button" id="grp_inv_generate_btn" class="btn btn-success">Generate <i class="fas fa-random"></i></button>\n' +
                '                </div>\n' +
                '            </div>\n' +
                '        </form>\n' +
                '    </div>\n' +
                '</div>'
        },
        thread_show_invite : function(invite){
            return '<div id="grp_inv_show" class="row">\n' +
                '    <div class="col-12">\n' +
                '        <div class="card">\n' +
                '            <div class="card-body bg-gradient-dark shadow rounded">\n' +
                '            <div class="col-12 mt-n2 mb-2 text-light text-center"><span class="h6">Share this link with others to grant access to this group!</span></div>\n' +
                '                <div class="col-12 px-0">\n' +
                '                    <form>\n' +
                '                        <div class="input-group">\n' +
                '                            <input id="inv_generated_link" class="form-control" value="'+invite.slug+'" readonly>\n' +
                '                            <div class="input-group-append">\n' +
                '                                <button type="button" id="grp_inv_copy_btn" class="btn btn-md btn-primary"><i class="far fa-copy"></i> Copy</button>\n' +
                '                            </div>\n' +
                '                        </div>\n' +
                '                    </form>\n' +
                '                </div>\n' +
                '                <div class="col-12 my-3">\n' +
                '                    <div class="row justify-content-center h5">\n' +
                '                        <span class="mb-2 mx-1 badge badge-pill badge-warning">Max Use: '+(invite.max_use > 0 ? invite.max_use : "No limit")+'</span>\n' +
                '                        <span class="mb-2 mx-1 badge badge-pill badge-warning">Current Use: '+invite.uses+'</span>\n' +
                '                        <span class="mb-2 mx-1 badge badge-pill badge-warning">Expires: '+(invite.expires ? TippinManager.format().makeTimeAgo(invite.expires) : "Never")+'</span>\n' +
                '                    </div>\n' +
                '                </div>\n' +
                '                <button id="grp_inv_remove_btn" type="button" class="btn btn-sm btn-block btn-danger"><i class="fas fa-trash"></i> Remove</button>\n' +
                '            </div>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '    <div class="col-12 mt-3 text-center">\n' +
                '        <hr>\n' +
                '<span class="h5 text-danger">*Generating a new link will destroy the invite above</span>'+
                '        <div class="col-12 mt-2"><button type="button" id="grp_inv_switch_generate_btn" class="btn btn-success mt-3">Generate new link <i class="fas fa-random"></i></button></div> \n' +
                '    </div>\n' +
                '</div>'
        },
        new_group_base : function(){
            return '<div id="thread_header_area"><div class="dropdown float-right">\n' +
                '<span id="thread_info_area">' +
                '<span id="thread_option_call"></span>' +
                '</span></span><span id="thread_error_area"></span>'+
                '<button onclick="ThreadManager.load().closeOpened()" title="Close" class="btn btn-lg text-danger btn-light pt-1 pb-0 px-2 mr-1" type="button"><i class="fas fa-times fa-2x"></i></button>'+
                '</div><div class="h3 font-weight-bold"><div class="d-inline-block mt-2 ml-2"><i class="fas fa-edit"></i> Create a group</div></div>'+
                '</div>'+
                '<div class="card messages-panel mt-1">\n' +
                '    <div class="message-body" id="thread_new_group">\n' +
                '        <div class="message-chat">\n' +
                '            <div id="msg_thread_new_group" class="chat-body pb-0 mb-0">\n'+
                '               <div id="messages_container_new_group">'+templates.loader()+'</div>\n'+
                '            </div>\n' +
                '            <div class="chat-footer">\n' +
                '                <div class="card bg-light mb-0 border-0">\n' +
                '                    <div class="col-12 mt-3 px-0">\n' +
                '                        <form class="form-inline w-100 needs-validation" action="javascript:ThreadManager.newForms().newGroup()" id="new_group_form" novalidate>\n' +
                '                            <div class="col-12">\n' +
                '                            <div class="input-group">\n' +
                '                                <input maxlength="50" class="form-control" id="subject" placeholder="Name the group conversation" name="subject" autocomplete="off" required>\n' +
                '                                <div class="input-group-append">\n' +
                '                                    <button id="make_thread_btn" class="btn btn-primary"><i class="fas fa-edit"></i> Create</button>\n' +
                '                                </div>\n' +
                '                                <div class="invalid-feedback mb-n4">Required / 50 characters or less</div>'+
                '                            </div>'+
                '                            </div>\n' +
                '                            <div class="col-12 my-3"></div>\n' +
                '                        </form>\n' +
                '                    </div>\n' +
                '                </div>\n' +
                '            </div>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '</div>'
        },
        search_base : function(){
            return '<div id="thread_header_area"><div class="dropdown float-right">\n' +
                '<span id="thread_info_area">' +
                '<span id="thread_option_call"></span>' +
                '</span></span><span id="thread_error_area"></span>'+
                '<button onclick="ThreadManager.load().closeOpened()" title="Close" class="btn btn-lg text-danger btn-light pt-1 pb-0 px-2 mr-1" type="button"><i class="fas fa-times fa-2x"></i></button>'+
                '</div><div class="h3 font-weight-bold">' +
                '<div class="form-inline ml-2">\n' +
                '  <div class="form-row w-100 mt-1">\n' +
                '      <input autocomplete="off" id="messenger_search_profiles" type="search" class="shadow-sm form-control w-100" placeholder="Search profiles...">\n' +
                '  </div>\n' +
                '</div></div>' +
                '</div>'+
                '<div class="card messages-panel mt-1">\n' +
                '    <div class="message-body" id="thread_new_group">\n' +
                '        <div class="message-chat mb-1">\n' +
                '            <div id="loading_thread" class="chat-body chat-special pb-0 mb-0">\n'+
                '               <ul id="messenger_search_content" class="messages-list">'+templates.thread_empty_search(false)+'</ul>\n'+
                '            </div>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '</div>'
        },
        loading_thread_base : function(){
            return '<div id="thread_header_area"><div class="dropdown float-right">\n' +
                '<span id="thread_info_area">' +
                '<span id="thread_option_call"></span>' +
                '</span></span><span id="thread_error_area"></span>'+
                '<button onclick="ThreadManager.load().closeOpened()" title="Close" class="btn btn-lg text-danger btn-light pt-1 pb-0 px-2 mr-1" type="button"><i class="fas fa-times fa-2x"></i></button>'+
                '</div><div class="h3 font-weight-bold"><div class="d-inline-block mt-2 ml-2"><div class="spinner-border text-primary" role="status"></div></div></div>'+
                '</div>'+
                '<div class="card messages-panel mt-1">\n' +
                '    <div class="message-body" id="thread_new_group">\n' +
                '        <div class="message-chat">\n' +
                '            <div id="loading_thread" class="chat-body pb-0 mb-0">\n'+
                '               <div id="loading_thread_content">'+templates.loader()+'</div>\n'+
                '            </div>\n' +
                '            <div class="chat-footer">\n' +
                '                <div class="card bg-light mb-0 border-0">\n' +
                '                    <div class="col-12 mt-3 px-0">\n' +
                '                        <form class="form-inline w-100" novalidate>\n' +
                '                            <div class="col-12">\n' +
                '                                <div class="form-group form-group-xs-nm">\n' +
                '                                    <input disabled autocomplete="off" type="text" class="form-control w-100"/>\n' +
                '                                </div>\n' +
                '                            </div>\n' +
                '                            <div class="col-12 my-3"></div>\n' +
                '                        </form>\n' +
                '                    </div>\n' +
                '                </div>\n' +
                '            </div>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '</div>'
        },
        contacts_base : function(){
            return '<div id="thread_header_area"><div class="dropdown float-right">\n' +
                '<span id="thread_info_area">' +
                '<span id="thread_option_call"></span>' +
                '</span></span><span id="thread_error_area"></span>'+
                '<button onclick="ThreadManager.load().closeOpened()" title="Close" class="btn btn-lg text-danger btn-light pt-1 pb-0 px-2 mr-1" type="button"><i class="fas fa-times fa-2x"></i></button>'+
                '</div><div class="h3 font-weight-bold">' +
                '<div class="d-inline-block mt-2 ml-2"><i class="far fa-address-book"></i> Contacts</div>' +
                '</div></div>'+
                '<div class="card messages-panel mt-1">\n' +
                '    <div class="message-body" id="thread_new_group">\n' +
                '        <div class="message-chat mb-1">\n' +
                '            <div id="loading_thread" class="chat-body chat-special pb-0 mb-0">\n'+
                '               <div id="messenger_contacts_ctnr">'+templates.loader()+'</div>\n'+
                '            </div>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '</div>'
        },
        thread_base : function(data, creating){
            return '<div class="card messages-panel mt-1">\n' +
                '    <div class="message-body" id="thread_'+(creating ? '' : data.thread_id)+'">\n' +
                '        <div class="message-chat">\n' +
                '            <div id="msg_thread_'+(creating ? '' : data.thread_id)+'" class="chat-body pb-0 mb-0">\n'+
                '               <div id="messages_container_'+(creating ? '' : data.thread_id)+'">'+(creating ? templates.thread_new_fill(data) : templates.loader())+'</div>\n'+
                '               <div id="pending_messages" class="w-100"></div>\n' +
                '               <div id="seen-by_final" class="seen-by-final w-100"></div>\n' +
                '               <div id="new_message_alert" class="pointer_area NS">' +
                templates.thread_new_message_alert() +
                '               </div>'+
                '            </div>\n' +
                '            <div class="chat-footer">\n' +
                (creating ? '' : (('options' in data && data.options.lockout) || (!data.options.send_message && !data.options.admin) ? templates.messages_disabled_overlay() : ''))+
                '                <div class="card bg-light mb-0 border-0">\n' +
                '                    <div class="col-12 mt-3 px-0">\n' +
                '                        <form class="form-inline w-100" id="thread_form">\n' +
                '                            <div class="col-12">\n' +
                '                                <div class="form-group form-group-xs-nm">\n' +
                '                                    <input disabled autocomplete="off" autocorrect="on" spellcheck="true" type="text" title="message" name="message_alt" id="emojionearea" class="form-control w-100 '+(TippinManager.common().mobile ? 'pr-special-btn' : '')+'"/>\n' +
                '                                </div>\n' +
                '                            </div>\n' +
                '                            <div class="col-12 my-1">\n' +
                '                                <div class="form-group form-group-xs-nm">\n' +
                '                                    <button id="file_upload_btn" data-toggle="tooltip" title="Upload File(s)" data-placement="top" class="btn btn-sm btn-light" onclick="$(\'#doc_file\').trigger(\'click\');" type="button"><i class="fas fa-paperclip"></i></button>\n' +
                '                                    <button id="image_upload_btn" data-toggle="tooltip" title="Upload Image(s)" data-placement="top" class="mx-1 btn btn-sm btn-light" onclick="$(\'#image_file\').trigger(\'click\');" type="button"><i class="far fa-image"></i></button>\n' +
                '                                </div>\n' +
                '                            </div>\n' +
                '                        </form>\n' +
                '                    </div>\n' +
                '                    <input class="NS" multiple type="file" name="doc_file" id="doc_file" accept=".pdf, .doc, .ppt, .xls, .docx, .pptx, .xlsx, .rar, .zip">\n' +
                '                    <input class="NS" multiple id="image_file" type="file" name="image_file" accept="image/*">\n' +
                '                </div>\n' +
                '            </div>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '</div>'
        },
        render_private : function(data, party){
            return templates.thread_private_header(data, party) + templates.thread_base(data)
        },
        render_group : function(data){
            return templates.thread_group_header(data) + templates.thread_base(data)
        },
        render_new_private : function (data) {
            return templates.thread_new_header(data) + templates.thread_base(data, true)
        }
    };
    return {
        render : function(){
            return templates
        },
        mobile : methods.switch_mobile_view
    };
}());
