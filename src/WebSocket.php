<?php

namespace FRohlfing\WebSocket;

use ZMQ;
use ZMQContext;
use ZMQSocket;
use ZMQSocketException;

/**
 * This class is useful to send a message to the websocket server.
 */
class WebSocket
{
    /**
     * ZMQSocket instance
     *
     * @var ZMQSocket
     */
    protected $zmqSocket;

    /**
     * Constructor.
     *
     * @param array $config Configuration for the websocket, see config.php for more details.
     * @throws ZMQSocketException
     */
    public function __construct($config)
    {
        if (!class_exists('\React\ZMQ\Context')) {
            throw new WebSocketException("react/zmq dependency is required to send messages from server!");
        }

        $context = new ZMQContext();
        $this->zmqSocket = $context->getSocket(ZMQ::SOCKET_PUSH, $config['zmq_push_id']);
        $this->zmqSocket->connect("tcp://127.0.0.1:" . $config['zmq_port']);
    }

    /**
     * Send a message to the websocket server.
     *
     * The websocket server will be dispatch the message to the push method of the WebSocketController.
     *
     * @param string $msg The message to send.
     * @throws ZMQSocketException
     */
    public function send($msg)
    {
        $this->zmqSocket->send($msg);
    }
}