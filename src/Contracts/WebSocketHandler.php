<?php

namespace FRohlfing\WebSocket\Contracts;

use Ratchet\MessageComponentInterface;

interface WebSocketHandler extends MessageComponentInterface
{
    /**
     * Received a message from the web server (via ZMQSocket).
     *
     * @param string $msg
     */
    public function onPush($msg);

    /**
     * If there is an error with the push socket handler, the Exception is handled by the Server and bubbled back up
     * through this method.
     *
     * @param \Exception $e
     */
    public function onPushError(\Exception $e);
}
