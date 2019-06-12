@extends('layouts.app')

@section('styles')
    <style>
        #chat-log {
            font-family: monospace;
            background: black;
            color: white;
            height: 400px;
            padding: 5px;
            border: 1px solid gray;
            overflow: scroll;
        }
    </style>
@endsection

@section('metas')
    <meta name="websocket-address" content="{{ (config('websocket.ssl_crt') ? 'wss://' : 'ws://') . $_SERVER['SERVER_NAME'] . ':' . config('websocket.port') }}" />
    <meta name="websocket-reconnect-delay" content="{{ config('websocket.reconnect_delay') }}" />
@endsection

@section('scripts')
    <script src="{{asset('js/websocket.js')}}"></script>
    <script>
        jQuery(document).ready(function() {
            $('#ping-button').click(function() {
                trace('The webserver sends a ping...');
                $.ajax({
                    method: 'GET',
                    url: $(this).data('url')
                }).fail(function(error) {
                    trace(error.statusText);
                }).done(function(response) {
                    trace('Ping sent');
                });
                // axios({
                //     method: 'get',
                //     url: $(this).data('url')
                // }).catch(function (error) {
                //     trace(error.response.statusText,);
                // }).then(function (response) {
                //     trace('Ping sent');
                // });
            });

            $('#connectBtn').click(function() {
                websocket.open();
            });

            $('#disconnectBtn').click(function() {
                websocket.close();
            });

            $('#message').keypress(function(event) {
                if (event.keyCode === 13) {
                    send();
                }
            });

            $('#sendBtn').click(function() {
                send();
            });

            websocket.on('open', function() {
                trace('Socket open.');
            });

            websocket.on('close', function(code, reason) {
                trace('Socket closed: ' + reason + ' (' + code + ')');
            });

            websocket.on('message', function(message) {
                message = JSON.parse(message);
                trace('Received: Sender=' + message.Sender + ', Event=' + message.Event + ', Data=' + JSON.stringify(message.Data));
            });

            websocket.on('error', function() {
                trace('Socket error!');
            });

            websocket.open();
        });

        function send()
        {
            if (!websocket.isReady()) {
                trace('Socket is not ready!');
                return;
            }

            var input = $('#message');
            var value = input.val();
            if (value === '') {
                trace('Please enter a message');
                return;
            }

            var message = { Sender: 'AGENT', Event: 'CHAT', Data: {Text: value} };

            websocket.send(JSON.stringify(message));

            trace('Sent: ' + value);
            input.val('');
        }

        function trace(message)
        {
            var div = $('#chat-log');
            div.append(message + '<br/>');
            div.scrollTop(div.prop('scrollHeight'));
        }
    </script>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div>
                    @include('_message')
                    @include('_errors')
                </div>
                <div class="card">
                    <div class="card-header">Web Socket Example</div>
                    <div class="card-body" id="form">
                        <button id="connectBtn" class="btn btn-primary btn-sm">
                            Connect
                        </button>
                        <button id="disconnectBtn" class="btn btn-secondary btn-sm">
                            Disconnect
                        </button>
                        <button id="ping-button" class="btn btn-light btn-sm" data-url="{{ url('examples/websocket/ping') }}">
                            <i class="fas fa-heartbeat"></i> Push a ping from webserver
                        </button>
                        <br/>
                        <div id="chat-log">
                        </div>
                        <input type="text" id="message" placeholder="Type a message.">
                        <button id="sendBtn" class="btn btn-warning btn-sm">
                            Send
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
