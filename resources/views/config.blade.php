@extends('messenger::app')

@section('title')
    {{config('messenger-ui.site_name')}} - Configuration
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header h3 text-info"><i class="fas fa-server"></i> <pre class="d-inline">Environment</pre></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <h4>&bull; PHP 7.4</h4>
                            </div>
                            <div class="col-12 col-md-6">
                                <h4>&bull; Ubuntu 20</h4>
                            </div>
                            <div class="col-12 col-md-6">
                                <h4>&bull; Laravel 8.x</h4>
                            </div>
                            <div class="col-12 col-md-6">
                                <h4>&bull; MySQL 8</h4>
                            </div>
                            <div class="col-12 col-md-6">
                                <h4>&bull; <a href="https://github.com/phpredis/phpredis/blob/develop/INSTALL.markdown" target="_blank">PHPRedis</a></h4>
                            </div>
                            <div class="col-12 col-md-6">
                                <h4>&bull; <a href="https://github.com/RTippin/messenger" target="_blank">Messenger core</a></h4>
                            </div>
                            <div class="col-12 col-md-6">
                                <h4>&bull; <a href="https://janus.conf.meetecho.com/docs/index.html" target="_blank">Janus media server</a></h4>
                            </div>
                            <div class="col-12 col-md-6">
                                <h4>&bull; <a href="https://github.com/RTippin/messenger-bots" target="_blank">Messenger bots</a></h4>
                            </div>
                            <div class="col-12 col-md-6">
                                <h4>&bull; <a href="https://github.com/RTippin/messenger-faker" target="_blank">Messenger faker</a></h4>
                            </div>
                            <div class="col-12 col-md-6">
                                <h4>&bull; <a href="https://github.com/RTippin/messenger-ui" target="_blank">Messenger UI</a></h4>
                            </div>
                            <div class="col-12 col-md-6">
                                <h4>&bull; <a href="https://github.com/RTippin/janus-client" target="_blank">Janus client</a></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mt-5">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header h3 text-info"><i class="fas fa-server"></i> <pre class="d-inline">Messenger::getConfig();</pre></div>
                    <div class="card-body">
                        @dump(messenger()->getConfig())
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mt-5">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header h3 text-info"><i class="fas fa-server"></i> <pre class="d-inline">Messenger::getProvider();</pre></div>
                    <div class="card-body">
                        @dump(messenger()->getProvider())
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
