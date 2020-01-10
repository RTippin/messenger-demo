@extends('layouts.app')
@section('seo')
    @include('seo.contact')
@endsection
@push('css')
    @include('layouts.bgGradient')
@endpush
@push('js') <script src='https://www.google.com/recaptcha/api.js'></script> @endpush
@section('content')
<div class="container">
    <div class="jumbotron bg-gradient-dark text-light">
        <div class="float-right d-none d-sm-block">
            <img id="RTlog" height="95" src="{{asset('images/tipz.png')}}">
        </div>
        <h3 class="display-4"><i class="fas fa-envelope"></i> Contact Us</h3>
        <p class="lead">Have questions or concerns? Feel free to send us a message below!</p>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-12 col-lg-8 offset-lg-2">
            <div class="card shadow-lg">
                <div class="card-body bg-light">
                    <div id="contact_sec">
                        <div class="row mt-1">
                            <div class="col-12">
                                <form class="needs-validation" id="contact_us" action="javascript:TippinManager.forms().ContactUs();" novalidate>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="your_name">Your Name</label>
                                            <input type="text" class="form-control" id="your_name" autocomplete="name" placeholder="Your Name" minlength="3" name="your_name" required value="{{messenger_profile() ? messenger_profile()->name : ""}}">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="your_email">Your Email</label>
                                            <input type="email" class="form-control" id="your_email" placeholder="Your Email" name="your_email" required value="{{messenger_profile() ? messenger_profile()->email : ""}}">
                                        </div>
                                    </div>
                                    <div class="form-group txtc">
                                        <label for="your_message">Your Message:</label>
                                        <textarea class="autoExpand form-control" required minlength="50" id="your_message" name="your_message" placeholder="Please leave your message here. Be as descriptive as you can!"></textarea>
                                        <div class="invalid-feedback">Must be at least 50 characters</div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-12">
                                            <div class="offset-md-3">
                                                <div class="g-recaptcha" data-sitekey="{{config('services.recaptcha.key')}}"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="text-center">
                                            <button id="sendBTN" class="btn btn-md btn-success"><i class="far fa-envelope"></i> Send Message</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div id="sent_sec" class="NS">
                        <div class="row">
                            <div class="col-12 mt-2">
                                <h3 class="text-center text-dark"><strong><i class="far fa-comments"></i> Message Sent!</strong></h3>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-10 offset-md-1">
                                <h4 class="text-secondary"><strong><span id="sent_response"></span></strong></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('special-js')
    <script>
        PageListeners.listen().txtAutosize();
        PageListeners.listen().animateLogo({elm : "#RTlog"});
    </script>
@endpush
