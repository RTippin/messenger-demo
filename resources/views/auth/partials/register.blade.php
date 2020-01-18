<div class="container">
    <div class="row">
        <div class="col-12 col-lg-8 offset-lg-2">
            <noscript>
                <div class="alert alert-danger shadow h4"><span class="float-right"><i class="fab fa-js-square fa-2x"></i></span> It appears your browser has javascript disabled. To continue using our website, you must first
                    <a class="alert-link" rel="nofollow" target="_blank" href="https://www.enable-javascript.com/"> enable javascript</a></div>
            </noscript>
            <div class="card shadow-lg">
                <div class="card-body bg-gradient-light text-dark">
                    <form id="regForm" class="needs-validation" role="form" method="POST" action="javascript:GuestManager.register()" novalidate>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="firstName">First Name</label>
                                <input minlength="2" type="text" class="form-control" id="firstName" placeholder="First Name..." name="firstName" autocomplete="given-name" required autofocus>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="lastName">Last Name</label>
                                <input minlength="2" type="text" class="form-control" id="lastName" placeholder="Last Name..." name="lastName" autocomplete="family-name" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col">Email</label>
                            <div class="col-sm-12">
                                <input id="emailR" type="email" class="form-control" placeholder="Email address..." name="email" autocomplete="email" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="password">Password</label>
                                <input aria-describedby="passwordHelpBlock" pattern="^(?=\S*?[A-Z])(?=\S*?[a-z])((?=\S*?[0-9])|(?=\S*?[^\w\*]))\S{8,}$" id="new_password" autocomplete="new-password" type="password"
                                       class="form-control" name="password" placeholder="Password..." required title="Password Strength">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="password_confirmation">Confirm Password</label>
                                <input pattern="^(?=\S*?[A-Z])(?=\S*?[a-z])((?=\S*?[0-9])|(?=\S*?[^\w\*]))\S{8,}$" id="password-confirm" autocomplete="new-password" placeholder="Confirm password..."
                                       type="password" class="form-control" name="password_confirmation" required>
                            </div>
                            <div class="form-group col-12">
                                <small id="passwordHelpBlock" class="form-text text-muted">
                                    *Password must be at least 8 characters long, contain one upper case letter, one lower case letter and (one number OR one special character). May NOT contain spaces.
                                </small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12">
                                <div class="offset-md-3">
                                    <div class="g-recaptcha" data-sitekey="{{config('services.recaptcha.key')}}"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row text-center mt-4">
                            <div class="col-12">
                                <button id="regBtn" type="submit" class="btn btn-lg btn-success">Sign Up <i class="fas fa-user-check"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
