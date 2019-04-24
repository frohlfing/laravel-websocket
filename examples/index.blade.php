@extends('app')

@section('scripts')
<script>

    var socket;
    var host = '{{ $_SERVER['SERVER_NAME'] }}'; // SERVER_ADDR
    var port = '{{ config('websocket.ws_port') }}';
    var url = 'ws://'+host+':'+port;

    function trace(msg) {
        var div = $('#chat-log');
        div.append(msg + '<br/>');
        div.scrollTop(div.prop('scrollHeight'));
    }

    $(document).ready(function() {

        $('#message').keypress(function(event) {
            if (event.keyCode == '13') {
                send();
            }
        });

        $('#sendBtn').click(function() {
            send();
        });

        $('#connectBtn').click(function() {
            connect();
        });

        $('#disconnectBtn').click(function() {
            socket.close()
        });

        connect();
    });

    function connect() {
        try {
            if ('WebSocket' in window) { // !window.WebSocket
                socket = new WebSocket(url);
            }
            else if ('MozWebSocket' in window) { // !window.MozWebSocket , old version of Firefox
                socket = new MozWebSocket(url);
            }
            else {
                alert('WebSocket not supported by this browser');
                return;
            }

            trace("Socket state: " + socket.readyState);

            socket.onopen = function() {
                trace('Socket open'); // socket.readyState == 1
            };

            socket.onclose = function(event) {
                if (event.code == 1000)
                    reason = 'Normal closure, meaning that the purpose for which the connection was established has been fulfilled.';
                else if(event.code == 1001)
                    reason = 'An endpoint is "going away", such as a server going down or a browser having navigated away from a page.';
                else if(event.code == 1002)
                    reason = 'An endpoint is terminating the connection due to a protocol error';
                else if(event.code == 1003)
                    reason = 'An endpoint is terminating the connection because it has received a type of data it cannot accept (e.g., an endpoint that understands only text data MAY send this if it receives a binary message).';
                else if(event.code == 1004)
                    reason = 'Reserved. The specific meaning might be defined in the future.';
                else if(event.code == 1005)
                    reason = 'No status code was actually present.';
                else if(event.code == 1006)
                    reason = 'The connection was closed abnormally, e.g., without sending or receiving a Close control frame';
                else if(event.code == 1007)
                    reason = 'An endpoint is terminating the connection because it has received data within a message that was not consistent with the type of the message (e.g., non-UTF-8 [http://tools.ietf.org/html/rfc3629] data within a text message).';
                else if(event.code == 1008)
                    reason = 'An endpoint is terminating the connection because it has received a message that "violates its policy". This reason is given either if there is no other sutible reason, or if there is a need to hide specific details about the policy.';
                else if(event.code == 1009)
                    reason = 'An endpoint is terminating the connection because it has received a message that is too big for it to process.';
                else if(event.code == 1010) // Note that this status code is not used by the server, because it can fail the WebSocket handshake instead.
                    reason = 'An endpoint (client) is terminating the connection because it has expected the server to negotiate one or more extension, but the server didn\'t return them in the response message of the WebSocket handshake. Specifically, the extensions that are needed are: ' + event.reason;
                else if(event.code == 1011)
                    reason = 'A server is terminating the connection because it encountered an unexpected condition that prevented it from fulfilling the request.';
                else if(event.code == 1015)
                    reason = 'The connection was closed due to a failure to perform a TLS handshake (e.g., the server certificate can\'t be verified).';
                else
                    reason = 'Unknown reason';

                trace('Socket closed: ' + reason); // socket.readyState == 0
            };

            socket.onmessage = function(event) {
                var data = JSON.parse(event.data);
                trace('Received: ' + data.message);
            };

            socket.onerror=function() {
                trace('Socket error!');
            };

        }
        catch(exception) {
            trace('Error: ' + exception);
        }
    }

    function send() {
        if (socket.readyState !== 1) {
            trace('Socket not ready (state='+socket.readyState+')!');
            return;
        }

        var inputField = $('#message');
        var text = inputField.val();
        if (text == '') {
            trace('Please enter a message');
            return;
        }
        var data = {message: text, param2: ['Bla', 'Blub', 47]};

        try {
            socket.send(JSON.stringify(data)); // Daten in ein JSON-String umwandeln und versenden
            trace('Sent: ' + text)
        }
        catch(exception) {
            trace('Failed to send');
        }

        inputField.val('');
    }

</script>
@endsection

@section('styles')
<style>
    #chat-log {
        height: 400px;
        border: 1px solid gray;
        overflow: scroll;
        padding: 5px;
    }
</style>
@endsection

@section('content')

<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">WebSocket Example Chat App</div>
				<div class="panel-body" id="form">
                    <button id="connectBtn">Connect</button>
                    <button id="disconnectBtn">Disconnect</button><br/>
                    <div id="chat-log">
                    </div>
                    <input type="text" id="message" placeholder="Type a message.">
                    <button id="sendBtn">Send</button>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection
