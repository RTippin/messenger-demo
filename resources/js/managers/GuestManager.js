window.GuestManager = (function () {
    var opt = {
        lock : true,
        bad_token : false
    },
    Initialize = {
        init : function(){
            opt.lock = false;
            setInterval(methods.heartbeat, 120000)
        }
    },
    methods = {
        heartbeat : function(){
            let pass = function(data){
                if(data.auth){
                    location.reload();
                }
            },
            fail = function(){
                window.location.reload()
            };
            TippinManager.heartbeat().gather(pass, fail);
        },
        errorTokenRetry : function(type, option){
            if(opt.bad_token){
                window.location.reload();
                return;
            }
            opt.bad_token = true;
            TippinManager.heartbeat().gather(function (data) {
                if(data.auth){
                    location.reload();
                }
                else{
                    methods[type](option)
                }
            }, function () {
                window.location.reload()
            })
        },
        specialLogin : function(email){
            if(opt.lock) return;
            TippinManager.alert().Modal({
                size : 'sm',
                icon : 'sign-in-alt',
                pre_loader : true,
                centered : true,
                unlock_buttons : false,
                allow_close : false,
                backdrop_ctrl : false,
                title: 'Logging in',
                theme: 'primary'
            });
            opt.lock = true;
            TippinManager.xhr().payload({
                route : '/login',
                data : {
                    email : email,
                    password : 'Messenger1!'
                },
                success : function(){
                    location.replace('/');
                },
                fail_alert : true
            });
        },
        Login : function(){
            if(opt.lock) return;
            opt.lock = true;
            TippinManager.button().addLoader({id : '#login_btn'});
            TippinManager.xhr().payload({
                route : '/login',
                data : {
                    email : $("#email").val(),
                    password : $("#password").val(),
                    remember : $("input[name=remember]:checked").val()
                },
                success : function(){
                    $("#login_form").replaceWith(TippinManager.alert().loader());
                    window.location.reload();
                },
                fail : function(error){
                    if(error.status === 403
                        && "data" in error
                        && "error" in error.data
                        && error.data.error === 66
                    ){
                        methods.errorTokenRetry('Login');
                        return;
                    }
                    let login_err = $("#login_err");
                    $("#login_form .form-group").addClass('has-error', 500);
                    switch (error.data.type) {
                        case 0:
                            login_err.html(error.data.error).show();
                        break;
                        case 1:
                        case 2:
                            login_err.html('Your account is not active').show();
                            TippinManager.alert().Modal({
                                theme : 'danger',
                                icon : 'lock',
                                title : 'Account Locked',
                                body : error.data.error
                            });
                        break;
                        case 3:
                            TippinManager.handle().xhrError({type : 2, response : error, no_close : true});
                        break;
                        default:
                            if(error.status === 429){
                                login_err.html(error.data.errors.email).show();
                                return;
                            }
                            login_err.html("Error: Code "+error.status).show();
                    }
                }
            });
        },
        Register : function(){
            if(opt.lock) return;
            opt.lock = true;
            TippinManager.button().addLoader({id : '#regBtn'});
            let form = new FormData();
            form.append('first', $("#firstName").val());
            form.append('last', $("#lastName").val());
            form.append('email', $("#emailR").val());
            form.append('password', $("#new_password").val());
            form.append('password_confirmation', $("#password-confirm").val());
            form.append('g-recaptcha-response', $("#g-recaptcha-response").val());
            TippinManager.xhr().payload({
                route: '/register',
                data : form,
                success: function () {
                    $("#regForm").replaceWith(TippinManager.alert().loader());
                    window.location.reload();
                },
                fail : function(error){
                    if(error.status === 403
                        && "data" in error
                        && "error" in error.data
                        && error.data.error === 66
                    ){
                        methods.errorTokenRetry('Register');
                        return;
                    }
                    grecaptcha.reset()
                },
                bypass : true
            });
        }
    };
    return {
        init : Initialize.init,
        login : methods.Login,
        special : methods.specialLogin,
        register : methods.Register,
        lock : function(arg){
            if(typeof arg === 'boolean') opt.lock = arg
        }
    };
}());
