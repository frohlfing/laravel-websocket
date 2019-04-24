<?php

namespace FRohlfing\WebSocket;

/**
 * Controller to handle the events from the websocket server.
 *
 * Note, the controller is running in the context of the websocket server. It is not the user context. That means, the
 * php session is not the users session, it's the session of the websocket server!
 */
abstract class BaseWebSocketController {

    /**
     * The MessageComponent instance.
     *
     * @var MessageComponent
     */
    protected $messageComponent;

    /**
     * Initialise controller.
     *
     * @param MessageComponent $messageComponent
     */
    public function init(MessageComponent $messageComponent)
    {
        $this->messageComponent = $messageComponent;
    }

    /**
     * Get the list of all connections.
     *
     * @return \SplObjectStorage
     */
    public function getConnections()
    {
        return $this->messageComponent->getConnections();
    }

    /**
     * Number of connections.
     *
     * @return int
     */
    public function count()
    {
        return $this->messageComponent->getConnections()->count();
    }

    /**
     * Send a message to a client.
     *
     * @param \Ratchet\ConnectionInterface $to The connection which receive the message.
     * @param string $msg The message to send.
     */
    public function send($to, $msg)
    {
        $to->send($msg);
    }

    /**
     * Send data as json encoded string to a client.
     *
     * @param \Ratchet\ConnectionInterface $to The connection to receive the message.
     * @param mixed $data The data object to send.
     */
//    public function sendData($to, $data)
//    {
//        $this->send($to, json_encode($data));
//    }

    /**
     * Broadcast message to clients
     *
     * @param string $msg The message to send.
     * @param null|array $eligible If not null, the message is send only to clients which are in this array (whitelist).
     * @param null|array $exclude If not null, the message is send to clients which aren't in this array (blacklist).
     * @param null|callable $filter A function to filter the connections (optional).
     */
    public function broadcast($msg, array $eligible=null, array $exclude=null, callable $filter = null)
    {
        if ($eligible) {
            $eligible = array_map(function($conn) { return $conn->resourceId; }, $eligible);
        }

        if ($exclude) {
            $exclude = array_map(function($conn) { return $conn->resourceId; }, $exclude);
        }

        $connections = $this->getConnections();
        foreach ($connections as $conn) {

            if ($eligible && !in_array($conn->resourceId, $eligible)) {
                continue;
            }

            if ($exclude && in_array($conn->resourceId, $exclude)) {
                continue;
            }

            if ($filter && !$filter($conn)) {
                continue;
            }

            $conn->send($msg);
        }
    }

    /**
     * Broadcast data as json encoded string to clients
     *
     * @param mixed $data The Data to send.
     * @param null|array $eligible If not null, the message is send only to clients which are in this array (whitelist).
     * @param null|array $exclude If not null, the message is send to clients which aren't in this array (blacklist).
     * @param null|callable $filter A function to filter the connections (optional).
     */
//    public function broadcastData($data, array $eligible=null, array $exclude=null, callable $filter = null)
//    {
//        $this->broadcast(json_encode($data), $eligible, $exclude, $filter);
//    }

    /**
     * Broadcast message to clients which aren't in the exclude array (blacklist)
     *
     * @param string $msg The message to send.
     * @param array $exclude Blacklist
     */
    protected function broadcastExclude($msg, array $exclude)
    {
        $this->broadcast($msg, null, $exclude);
    }

    /**
     * Broadcast data as json encoded string to clients which aren't in the exclude array (blacklist)
     *
     * @param mixed $data The data to send.
     * @param array $exclude Blacklist
     */
//    protected function broadcastDataExclude($data, array $exclude)
//    {
//        $this->broadcast(json_encode($data), null, $exclude);
//    }

    /**
     * When a new connection is opened it will be passed to this method.
     *
     * @param \Ratchet\ConnectionInterface $conn The client that just connected to your application
     */
	abstract function onOpen($conn);

    /**
     * This is called before or after a socket is closed (depends on how it's closed).
     * SendMessage to $conn will not result in an error if it has already been closed.
     *
     * @param \Ratchet\ConnectionInterface $conn The client connection that is closing/closed
     */
	abstract function onClose($conn);

    /**
     * Received a message from a client.
     *
     * @param \Ratchet\ConnectionInterface $from The connection that send the message.
     * @param $msg
     */
    abstract function onMessage($from, $msg);

    /**
     * Received a message from the webserver (via ZMQSocket).
     *
     * @param string $msg
     */
    abstract public function onPush($msg);

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through
     * this method.
     *
     * @param \Ratchet\ConnectionInterface $conn The connection that raced the execption.
     * @param \Exception $e
     */
	abstract function onError($conn, \Exception $e);

}