@extends('messenger::app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header h3"><i class="fas fa-comments"></i> Welcome to the demo!</div>
                    <div class="card-body">
                        <p class="lead">
                            This demo is running our package <a href="https://github.com/RTippin/messenger" target="_blank">rtippin/messenger</a>,
                            where you can find more information and install documentation. Calling in this demo is done using the default
                            <a href="https://github.com/RTippin/messenger/blob/master/src/Brokers/JanusBroker.php" target="_blank">Janus Broker</a>.
                            We are running our own instance of <a href="https://janus.conf.meetecho.com/" target="_blank">Janus Media Server</a>,
                            which you will have to do on your own if you wish to use calling, or create your own
                            <a href="https://github.com/RTippin/messenger/blob/master/src/Contracts/VideoDriver.php" target="_blank">Driver</a>.
                        </p>
                        <hr>
                        <p class="text-center h3">
                            Ready to see your messenger?
                            <br><br>
                            <a class="btn btn-lg btn-warning" href="{{route('messenger.portal')}}"><i class="fas fa-comments"></i> View Portal</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header h3 text-info"><i class="fas fa-server"></i> <pre class="d-inline">Messenger::getConfig();</pre></div>
                    <div class="card-body">
                        @php
                            dump(messenger()->getConfig());
                        @endphp
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mt-3">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header h3 text-info"><i class="fas fa-server"></i> <pre class="d-inline">Messenger::getProvider();</pre></div>
                    <div class="card-body">
                        @php
                            dump(messenger()->getProvider());
                        @endphp
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
