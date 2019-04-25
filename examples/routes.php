<?php

use FRohlfing\WebSocket\Facades\WebSocket;
use Illuminate\Support\Facades\Route;

Route::get('websocket/chat','ChatController@index')->name('websocket.chat');

Route::get('websocket/push', function() {
    WebSocket::push(json_encode(['message' => 'foo']));
});

