<div class="container">
    <div class="row">
        <div class="col-12 col-lg-8 offset-lg-2">
            <noscript>
                <div class="alert alert-danger shadow h4"><span class="float-right"><i class="fab fa-js-square fa-2x"></i></span> It appears your browser has javascript disabled. To continue using our website, you must first
                    <a class="alert-link" rel="nofollow" target="_blank" href="https://www.enable-javascript.com/"> enable javascript</a></div>
            </noscript>
            <div class="card shadow-lg rounded-lg">
                <div class="card-body bg-light">
                    <div class="row mt-1">
                        <div class="col-12">
                            <form id="login_form" class="needs-validation" action="javascript:GuestManager.login()" novalidate>
                                <div class="form-row">
                                    <div class="form-group input-group-lg col-md-8 mx-auto">
                                        <label for="email">Email:</label>
                                        <input type="email" class="form-control" id="email" autocomplete="email" placeholder="Email" name="email" autofocus required">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group input-group-lg col-md-8 mx-auto">
                                        <label for="password">Password:</label>
                                        <input id="password" type="password" autocomplete="current-password" class="form-control" name="password" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group form-check input-group-lg col-md-8 mx-auto">
                                        <div class="custom-control custom-checkbox mb-3">
                                            <input type="checkbox" class="custom-control-input" name="remember" id="remember_me">
                                            <label class="custom-control-label" for="remember_me">Remember me?</label>
                                            <div id="login_err" class="text-danger ml-n4 mt-2"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="text-center">
                                        <button id="login_btn" class="btn btn-lg btn-success"><i class="fas fa-sign-in-alt"></i> Log In</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
