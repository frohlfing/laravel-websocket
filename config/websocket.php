<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Web Socket Handler
    |--------------------------------------------------------------------------
    |
    | Class to handle the events from the websocket server. The Class has to
    | implements the interface `FRohlfing\WebSocket\Contracts\WebSocketHandler`.
    |
    */

    'handler' => 'App\Sockets\WebSocketHandler',

    /*
    |--------------------------------------------------------------------------
    | Web Socket Default Port
    |--------------------------------------------------------------------------
    |
    | Default port on which the React Socket Server will listen for incoming
    | connections. You can also define a port in the artisan command, if nothing
    | is set there, we'll use this port.
    |
    */

    'port' => env('WEBSOCKET_PORT', 1111),

    /*
    |--------------------------------------------------------------------------
    | Port for push messages
    |--------------------------------------------------------------------------
    |
    | This is used for the communication between web server and web socket server.
    |
    */

    'push_port' => env('WEBSOCKET_PUSH_PORT', 5555),

    /*
    |--------------------------------------------------------------------------
    | Port for push messages
    |--------------------------------------------------------------------------
    |
    | Specifies how long the push socket blocks trying flush messages after it
    | has been closed (in ms, null means infinitely).
    |
    */

    'push_timeout' => env('WEBSOCKET_PUSH_TIMEOUT', 2000),

    /*
    |--------------------------------------------------------------------------
    | ZMQ socket push persistent id
    |--------------------------------------------------------------------------
    |
    | You should make it unique with environment if there are multi environment
    | on your server.
    | For more details see http://php.net/manual/de/zmqcontext.getsocket.php
    |
    */

    'zmq_push_id' => 'zmq.push',

    /*
    |--------------------------------------------------------------------------
    | ZMQ socket pull persistent id
    |--------------------------------------------------------------------------
    |
    | You should make it unique with environment if there are multi environment
    | on your server.
    | For more details see http://php.net/manual/de/zmqcontext.getsocket.php
    |
    */

    'zmq_pull_id' => 'zmq.pull',

    /*
    |--------------------------------------------------------------------------
    | Reconnect Delay
    |--------------------------------------------------------------------------
    |
    | Delay for a reconnect after the connection is lost (in ms, null means never)
    |
    */

    'reconnect_delay' => env('WEBSOCKET_RECONNECT_DELAY', 2000),

    /*
    |--------------------------------------------------------------------------
    | Log Level
    |--------------------------------------------------------------------------
    |
    | The verbosity of logging.
    |
    | "none", "error", "info" or "debug"
    |
    */

    'logLevel' => env('WEBSOCKET_LOGLEVEL', 'error'),

];