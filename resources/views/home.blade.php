@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    You are logged in!
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('javascript')
    <script>

        var centrifuge = new Centrifuge('ws://localhost:8569/connection/websocket');
        // var centrifuge = new Centrifuge('http://centrifugo:8000/connection/sockjs', {
        //     sockjsTransports: ["websocket", "xhr-streaming"]
        // });
        centrifuge.setToken('<?php echo $token; ?>');
        centrifuge.subscribe("news", function(message) {
            console.log(message.data.message);
        });
        centrifuge.disconnect("news", function(message) {
            console.log(message);
        });
        centrifuge.connect();
    </script>
@endsection