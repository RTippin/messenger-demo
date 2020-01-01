window.PageListeners = (function () {
    var opt = {
        FSinterval : null,
        animations : ['flip', 'rubberBand', 'bounce', 'swing', 'tada', 'jello'],
        email_suggestion : null,
        email_input : null,
        knok_interval : null
    },
    mounted = {
        init : function(){
            $("#nav_search_form").on('submit', function(){
                TippinManager.button().addLoader({id : '#nav_search_btn'})
            });
            methods.validateForms()
        }
    },
    methods = {
        validateForms : function(){
            let forms = document.getElementsByClassName('needs-validation');
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                        $(".invalid-always-show").show()
                    }
                    else {
                        $(".invalid-always-show").hide()
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        },
        tooltips : function(){
            let tips = $('.tooltip');
            if(tips.length) tips.remove();
            $('[data-toggle="tooltip"]').tooltip({container: "body"});
            $('[data-tooltip="tooltip"]').tooltip({container: "body"})
        },
        txtAutosize : function(){
            autosize(document.querySelectorAll('.autoExpand'))
        },
        txtAutosizeUpdate : function(){
            autosize.update(document.querySelectorAll('.autoExpand'))
        },
        animateKnok : function(state){
            if(!state){
                clearInterval(opt.knok_interval);
                return;
            }
            if(opt.knok_interval) clearInterval(opt.knok_interval);
            let elm = $("#knok_animate");
            elm.removeClass().addClass('wobble animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                $(this).removeClass();
            });
            opt.knok_interval = setInterval(function(){
                elm.removeClass().addClass('wobble animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                    $(this).removeClass();
                });
            }, 3000);

        },
        animateLogo : function(arg){
            let logo = $(arg.elm);
            if("off" in arg){
                clearInterval(opt.FSinterval);
                return;
            }
            if(!logo.length || !logo.is(":visible")) return;
            logo.removeClass().addClass('flip animated')
            .one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                $(this).removeClass();
            });
            opt.FSinterval = setInterval(function(){
                logo.removeClass().addClass(opt.animations[Math.floor(Math.random() * opt.animations.length)]+' animated')
                .one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                    $(this).removeClass();
                });
            }, 4000);
        },
        triggerCommon : function(){
            methods.validateForms();
            methods.txtAutosize();
            methods.tooltips();
            methods.txtAutosizeUpdate();
        }
    };
    return {
        init : mounted.init,
        listen : function(){
            return methods
        }
    };
}());
