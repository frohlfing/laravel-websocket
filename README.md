# Web Socket for Laravel 5.7

This package is a smart solution to a web socket support for Laravel. It's based on [Ratchet](http://socketo.me/), a PHP library to build websocket server. 

## Supported Browser
- Chrome
- IE 10 and above
- Firefox
- Safari
- Opera
- iOS Safari
- Android Browser 4.4 and above
- Chrome for Android

# Requirements

[ZeroMQ](http://zeromq.org/bindings:php) is require to push messages from webserver to the clients.

Download:
	https://pecl.php.net/package/zmq/1.1.3/windows (Thread Safe, x86)

1) Copy libzmq.dll into your php directory (e.g. c:\xampp\php\)

2) Copy php_zmq.dll to your php extension directory (e.g. c:\xampp\php\ext\)

3) Add the following line to your php.ini:
	extension=php_zmq.dll

4) Restart your web server.

# Installation
    
Add this to the "repositories" property in `composer.json`:

    "repositories": [
        {
            "type": "git",
            "url": "git@bitbucket.org:frohlfing/laravel-websocket.git"
        }
    ],

Fetch the package by running the following command:

    composer require frohlfing/laravel-websocket:1.57.*@dev

Publish the assets by running the following command:

    php artisan vendor:publish --provider="FRohlfing\WebSocket\WebSocketServiceProvider" --tag="public"
	
Publish the configuration file:

    php artisan vendor:publish --provider="FRohlfing\WebSocket\WebSocketServiceProvider" --tag="config"
    
Next you need to edit `config/websocket.php`.   
  
# Usage

Copy `examples/WebSocketHandler.php.stub` to `app/Sockets/WebSocketHandler.php`.
Copy `examples/ChatController.php.stubp` to `app/Http/Controllers/ChatController.php`.
Copy `examples/chat.blade.php` to `resources/views/websocket/chat.blade.php`.
Copy the content of `examples/routes.php` into `routes/web.php`.
    
Start the websocket server via shell:

    php artisan websocket:serve
    
Open this URL with your browser to chat:

    http://<server>/websocket/chat

# Notes

This package support the base functions of websockets. If you need a WAMP server, you shoud visit the package [Latchet](https://github.com/sidneywidmer/Latchet).