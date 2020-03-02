window.NetworksManager = (function () {
    var opt = {
        lock : true
    },
    Initialize = {
      init : function(){
          opt.lock = false;
      }
    },
    templates = {
        add_to : function(data){
            if("dropdown" in data){
                return '<a class="dropdown-item network_option" onclick="NetworksManager.action({dropdown : true, owner_id : \''+data.owner_id+'\', action : \'add\', slug : \''+data.slug+'\', type : \''+data.type+'\'}); return false;" href="#">' +
                    '<i class="fas fa-user-plus"></i> Add friend</a>';
            }
            return '<button id="add_network_'+data.owner_id+'" data-toggle="tooltip" title="Add friend" data-placement="top" class="btn btn-success pt-1 pb-0 px-2" ' +
                'onclick="NetworksManager.action({action : \'add\', slug : \''+data.slug+'\', type : \''+data.type+'\', owner_id : \''+data.owner_id+'\'});"><i class="fas fa-user-plus fa-2x"></i></button>';
        },
        remove_from : function(data){
            if("dropdown" in data){
                return '<a class="dropdown-item network_option" onclick="NetworksManager.action({dropdown : true, owner_id : \''+data.owner_id+'\', action : \'remove\', slug : \''+data.slug+'\', type : \''+data.type+'\'}); return false;" href="#">' +
                    '<i class="fas fa-user-times"></i> Remove friend</a>';
            }
            return '<button id="remove_network_'+data.owner_id+'" data-toggle="tooltip" title="Remove friend" data-placement="top" class="btn btn-danger pt-1 pb-0 px-2" ' +
                'onclick="NetworksManager.action({action : \'remove\', slug : \''+data.slug+'\', type : \''+data.type+'\', owner_id : \''+data.owner_id+'\'});"><i class="fas fa-user-times fa-2x"></i></button>';
        },
        cancel_request : function(data){
            if("dropdown" in data){
                return '<a class="dropdown-item network_option" onclick="NetworksManager.action({dropdown : true, owner_id : \''+data.owner_id+'\', action : \'cancel\', slug : \''+data.slug+'\', type : \''+data.type+'\'}); return false;" href="#">' +
                    '<i class="fas fa-ban"></i> Cancel friend request</a>';
            }
            return '<button id="cancel_network_'+data.owner_id+'" data-toggle="tooltip" title="Cancel friend request" data-placement="top" class="btn btn-danger pt-1 pb-0 px-2" ' +
                'onclick="NetworksManager.action({action : \'cancel\', slug : \''+data.slug+'\', type : \''+data.type+'\', owner_id : \''+data.owner_id+'\'});"><i class="fas fa-ban fa-2x"></i></button>';
        }
    },
    methods = {
        perform : function(arg){
            if(opt.lock) return;
            if("owner_id" in arg){
                TippinManager.button().addLoader({id : '#'+arg.action+'_network_'+arg.owner_id});
            } else{
                TippinManager.button().addLoader({id : '#'+arg.action+'_network_'+arg.slug});
            }
            let construct = {
                route : '/demo-api/friends/'+arg.action,
                data : {
                    slug : arg.slug,
                    type : arg.type
                },
                shared : arg,
                success : methods.updatePage,
                fail_alert : true
            };
            if('exports' in arg){
                construct.exports =  Object.assign(arg, arg.exports);
            }
            TippinManager.xhr().payload(construct);
        },
        updatePage : function(data){
            let t = null;
            if("owner_id" in data){
                switch(data.case){
                    case 0:
                        t = templates.add_to(data);
                    break;
                    case 1:
                        t = templates.remove_from(data);
                    break;
                    case 2:
                        t = templates.cancel_request(data);
                    break;
                }
                let elm = $("#network_for_"+data.owner_id);
                t ? elm.html(t) : elm.html('');
            }
            TippinManager.alert().Alert({
                title : 'Friends',
                body : data.msg,
                toast : true,
                theme : (data.action === 'remove' || data.action === 'deny' ? 'error' : data.action === 'cancel' ? 'warning' : 'success')
            });
            PageListeners.listen().tooltips();
            NotifyManager.friends()
        }
    };
    return {
        action : methods.perform,
        init : Initialize.init,
        lock : function(arg){
            if(typeof arg === 'boolean') opt.lock = arg
        }
    };
}());
