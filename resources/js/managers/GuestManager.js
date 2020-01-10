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
    templates = {
        loginModal : function () {
            return '<div class="row mt-1">\n' +
                '\t<div class="col-12">\n' +
                '\t\t<form id="login_form">\n' +
                '\t\t\t<div class="form-row">\n' +
                '\t\t\t\t<div class="form-group input-group-lg col-10 mx-auto">\n' +
                '\t\t\t\t\t<label for="email_modal">Email:</label>\n' +
                '\t\t\t\t\t<input type="email" class="form-control" id="email_modal" autocomplete="email" placeholder="Email" name="email_modal" required>\n' +
                '\t\t\t\t</div>\n' +
                '\t\t\t</div>\n' +
                '\t\t\t<div class="form-row">\n' +
                '\t\t\t\t<div class="form-group input-group-lg col-10 mx-auto">\n' +
                '\t\t\t\t\t<label for="password_modal">Password:</label>\n' +
                '\t\t\t\t\t<input id="password_modal" type="password" autocomplete="current-password" class="form-control" name="password_modal" required>\n' +
                '\t\t\t\t</div>\n' +
                '\t\t\t</div>\n' +
                '\t\t\t<div class="form-row">\n' +
                '\t\t\t\t<div class="form-group form-check input-group-lg col-10 mx-auto">\n' +
                '\t\t\t\t\t<div class="custom-control custom-checkbox mb-1">\n' +
                '\t\t\t\t\t\t<input type="checkbox" class="custom-control-input" name="remember" id="remember_me">\n' +
                '\t\t\t\t\t\t<label class="custom-control-label" for="remember_me">Remember me?</label>\n' +
                '\t\t\t\t\t</div>\n' +
                '<div id="login_err" class="text-danger ml-n4 mt-2"></div>'+
                '\t\t\t\t</div>\n' +
                '\t\t\t</div>\n' +
                '\t\t</form>\n' +
                '\t</div>\n' +
                '</div>'
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
        showLoginModal : function(){
            TippinManager.alert().Modal({
                theme : 'primary',
                h4 : false,
                centered : true,
                backdrop_ctrl : false,
                icon : 'sign-in-alt',
                title : 'Messenger Log In',
                body : templates.loginModal(),
                cb_btn_txt : 'Log In',
                cb_btn_icon : 'sign-in-alt',
                cb_btn_theme : 'success',
                callback : function () {
                    methods.Login(true)
                },
                onReady : function () {
                    $("#login_form").keydown(function(event) {
                        if (event.keyCode === 13) $("#modal_cb_btn").click()
                    });
                    $("#email_modal").focus()
                }
            })
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
        Login : function(modal){
            if(opt.lock) return;
            opt.lock = true;
            TippinManager.button().addLoader({id : '#login_btn'});
            TippinManager.xhr().payload({
                route : '/login',
                data : {
                    email : modal ? $("#email_modal").val() : $("#email").val(),
                    password : modal ? $("#password_modal").val() : $("#password").val(),
                    remember : $("input[name=remember]:checked").val()
                },
                success : function(data){
                    let form_elm = $("#login_form");
                    if(modal){
                        if(form_elm.length) form_elm.replaceWith(TippinManager.alert().loader());
                        TippinManager.alert().fillModal({loader : true, no_close : true, body : null, title : 'Logging in...'});
                        window.location.reload();
                        return;
                    }
                    if("intended" in data){
                        form_elm.replaceWith(TippinManager.alert().loader());
                        data.intended === 'reload' ? window.location.reload() : window.location.replace(data.intended);
                    }
                    else{
                        location.replace('/');
                    }
                },
                fail : function(error){
                    if(error.status === 403
                        && "data" in error
                        && "error" in error.data
                        && error.data.error === 66
                    ){
                        methods.errorTokenRetry('Login', modal);
                        return;
                    }
                    let login_err = $("#login_err"),
                    modalError = function(msg){
                        TippinManager.alert().Alert({
                            toast : true,
                            theme : 'error',
                            title : msg
                        })
                    };
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
        Register : function(reload){
            if(opt.lock) return;
            opt.lock = true;
            TippinManager.button().addLoader({id : '#regBtn'});
            let form = new FormData();
            form.append('firstName', $("#firstName").val());
            form.append('lastName', $("#lastName").val());
            form.append('email', $("#emailR").val());
            form.append('password', $("#new_password").val());
            form.append('password_confirmation', $("#password-confirm").val());
            form.append('g-recaptcha-response', $("#g-recaptcha-response").val());
            TippinManager.xhr().payload({
                route: '/register',
                data : form,
                success: function (data) {
                    if(reload){
                        window.location.reload();
                        return;
                    }
                    location.replace("/messenger");
                },
                fail : function(error){
                    if(error.status === 403
                        && "data" in error
                        && "error" in error.data
                        && error.data.error === 66
                    ){
                        methods.errorTokenRetry('Register', reload);
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
        loginPopup : methods.showLoginModal,
        login : methods.Login,
        special : methods.specialLogin,
        register : methods.Register,
        lock : function(arg){
            if(typeof arg === 'boolean') opt.lock = arg
        }
    };
}());
