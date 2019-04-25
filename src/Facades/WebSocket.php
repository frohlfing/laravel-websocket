<?php

namespace FRohlfing\WebSocket\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void push($msg)
 * @method static void push2($msg)
 *
 * @see \FRohlfing\WebSocket\WebSocket
 */
class WebSocket extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \FRohlfing\WebSocket\Services\WebSocket::class;
    }
}