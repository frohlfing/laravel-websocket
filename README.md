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

Fetch the package by running the following command:

    composer require frohlfing/laravel-websocket:1.57.*@dev

Publish the assets by running the following command:

    php artisan vendor:publish --provider="FRohlfing\WebSocket\WebSocketServiceProvider" --tag="public"
	
Publish the configuration file:

    php artisan vendor:publish --provider="FRohlfing\WebSocket\WebSocketServiceProvider" --tag="config"
    
Next you may edit `config/websocket.php`.   
  
# Usage

Copy `examples/WebSocketHandler.php.stub` to `app/Sockets/WebSocketHandler.php`.

Copy `examples/ChatController.php.stubp` to `app/Http/Controllers/ChatController.php`.

Copy `examples/chat.blade.php` to `resources/views/websocket/chat.blade.php`.

Copy the content of `examples/routes.php` into `routes/web.php`.
    
Start the web socket server via shell:

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

However, it is better to use a process monitor such as [Supervisor](http://supervisord.org/) (on Mac and Linux) 
or [NSSM](http://nssm.cc) (on Windows) to automatically restart the Web Socket if it fails.

## Installation on Mac and Linux:

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

## Installation on Windows:

1) Install [NSSM](http://nssm.cc) - the Non-Sucking Service Manager.
2) Install a the web socket as windows service:

    ```bash
    nssm install "websocket" ImagePath "C:\xampp\php\php.exe" AppDirectory "C:\xampp\htdocs\laravel5" DisplayName "Web Socket Server" Description "Web Socket Server for Laravel"
    ```
    
    Parameters:    
    - Path:        `C:\xampp\php\php.exe`
    - Startup Dir: `C:\xampp\htdocs\laravel5`
    - Arguments:   `artisan websocket:serve`

# Notes

This package supports the basic functions of web sockets. If you need a WAMP server, you should visit the package 
[Latchet](https://github.com/sidneywidmer/Latchet).