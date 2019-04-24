<?php

namespace FRohlfing\WebSocket\Facades;

use Illuminate\Support\Facades\Facade;

class WebSocket extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'websocket';
    }
}