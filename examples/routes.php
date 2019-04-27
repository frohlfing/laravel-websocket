<?php

use Illuminate\Support\Facades\Route;

Route::get('websocket/chat',  'ChatController@index')->name('websocket.chat');
Route::get('websocket/ping',  'ChatController@ping')->name('websocket.ping');