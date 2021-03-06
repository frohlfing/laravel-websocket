# Web Socket for Laravel 5.7

This package is a smart solution to a web socket support for Laravel. It's based on [Ratchet](http://socketo.me/), a 
PHP library to build websocket server. 

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

# Installation
    
Add this to the "repositories" property in `composer.json`:

    "repositories": [
        {
            "type": "git",
            "url": "git@bitbucket.org:frohlfing/laravel-websocket.git"
        }
    ],

Fetch the package via composer:

    composer require frohlfing/laravel-websocket:1.57.*@dev

Publish the configuration file if you need to override the default settings:

    php artisan vendor:publish --provider="FRohlfing\WebSocket\WebSocketServiceProvider" --tag="config"
  
# Usage

Copy `examples/WebSocketHandler.php.stub` to `app/Sockets/WebSocketHandler.php`.

Copy `examples/ChatController.php.stubp` to `app/Http/Controllers/ChatController.php`.

Copy `examples/chat.blade.php` to `resources/views/websocket/chat.blade.php`.

Copy `resources/js/websocket.js` to `public/js/websocket.js`.

Copy the content of `examples/routes.php` into `routes/web.php`.
    
Start the web socket server:

    ```bash
    php artisan websocket:serve
    ```
        
Open this URL with your browser to chat:

    http://<server>/websocket/chat

# Background Service

You can run the web socket in the background like this:

    ```bash
    php artisan websocket:serve > /dev/null 2>&1 &
    ```

However, it is better to use a process monitor such as [Supervisor](http://supervisord.org/) (under Mac and Linux) 
or [NSSM](http://nssm.cc) (under Windows) to automatically restart the Web Socket if it fails.

## Installation under Mac and Linux

1) Install [Supervisor](http://supervisord.org/)
2) **On Mac:**
     
    Create `/usr/local/etc/supervisor/conf.d/websocket.conf`:
    
        [program:websocket]
        process_name=%(program_name)s_%(process_num)02d
        command=/Applications/XAMPP/xamppfiles/bin/php /Users/frank/htdocs/laravel5/artisan websocket:serve
        autostart=true
        autorestart=true
        user=daemon
        numprocs=1
        redirect_stderr=true
        stdout_logfile=/Users/frank/htdocs/laravel5/storage/logs/websocket.log  
 
    **On Linux:**
    
    Create `etc/supervisor/conf.d/websocket.conf`:
    
        [program:websocket]
        process_name=%(program_name)s_%(process_num)02d
        command=php /var/www/laravel5/artisan websocket:serve
        autostart=true
        autorestart=true
        user=www-data
        numprocs=1
        redirect_stderr=true
        stdout_logfile=/var/www/laravel5/storage/logs/websocket.log
         
 3) Apply changes:
    
    ```bash
    sudo supervisorctl reread
    sudo supervisorctl update
    ```
		
4) Check that the web socket runs:
	
	```bash
    supervisorctl status
    ```

## Installation under Windows

1) Install [NSSM](http://nssm.cc) - the Non-Sucking Service Manager.

2) Install the web socket as windows service:

    ```bash
    nssm install websocket "C:\xampp\php\php.exe" artisan websocket:serve
    nssm set websocket AppDirectory "C:\xampp\htdocs\laravel5"
    nssm set websocket DisplayName "Web Socket Server"
    nssm set websocket Description "Web Socket Server for Laravel" 
    nssm start websocket
    ```

3) Check that the web socket runs:
	
	```bash
    nssm status websocket
    ```

# Notes

This package supports the basic functions of web sockets. If you need a WAMP server, you should visit the package 
[Latchet](https://github.com/sidneywidmer/Latchet).