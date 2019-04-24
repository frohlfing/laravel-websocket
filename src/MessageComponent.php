<?php

namespace FRohlfing\WebSocket;

use Illuminate\Container\Container;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * This is the interface to build a Ratchet application with.
 *
 * It implements the decorator pattern to build an application stack (see Ratchet\ComponentInterface).
 */
class MessageComponent implements MessageComponentInterface {

    /**
     * List of all connections.
     *
     * @var \SplObjectStorage
     */
    protected $connections;

    /**
     * Instance of the WebSocketController.
     *
     * @var BaseWebSocketController
     */
    protected $controller;

    /**
     * Constructor.
     *
     * @param Container $container
     */
    public function __construct(BaseWebSocketController $controller)
    {
        $controller->init($this);
        $this->controller = $controller;
        $this->connections = new \SplObjectStorage;
    }

    /**
     * Get the list of all connections.
     *
     * @return \SplObjectStorage
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Called when a new client has connected.
     *
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->connections->attach($conn);
        $this->controller->onOpen($conn);
    }

    /**
     * Called when a connection is closed.
     *
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->controller->onClose($conn);
        $this->connections->detach($conn);
    }

    /**
     * Called when a message is received from a client.
     *
     * @param ConnectionInterface $from
     * @param string $msg
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $this->controller->onMessage($from, $msg);
    }

    /**
     * Called when a message is received from the webserver (via ZMQSocket).
     *
     * @param string $msg
     */
    public function onPush($msg)
    {
        $this->controller->onPush($msg);
        //$this->dispatch('push', compact('msg'));
    }

    /**
     * Called when an error occurs on a connection.
     *
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->controller->onError($conn, $e);
        //$this->dispatch('error', compact('conn', 'e'));
    }

}
