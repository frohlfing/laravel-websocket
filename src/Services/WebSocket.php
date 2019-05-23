<?php

namespace FRohlfing\WebSocket\Services;

use RuntimeException;
use Symfony\Component\Process\Process;
use ZMQ;
use ZMQContext;
use ZMQSocket;
use ZMQSocketException;

/**
 * Class to push messages to the web socket.
 *
 * @see http://socketo.me/docs/push Push Integration by Ratchet
 */
class WebSocket
{
    /**
     * ZMQSocket to send messages from the web server to the web socket.
     *
     * @var ZMQSocket
     */
    protected $pushSocket;

    /**
     * Configuration
     *
     * @var array
     */
    private $config;

    /**
     * Create a new WebSocket instance.
     * @param array $config Configuration for the web socket
     */
    public function __construct($config)
    {
        if (!class_exists('\React\ZMQ\Context')) {
            throw new RuntimeException('React/ZMQ is required to push messages!');
        }

        $this->config = $config;
    }

    /**
     * Push a message to the web socket server.
     *
     * @param string $message The message to send.
     * @throws ZMQSocketException
     */
    public function push($message)
    {
        if (!$this->pushSocket) {
            $context = new ZMQContext();
            $this->pushSocket = $context->getSocket(ZMQ::SOCKET_PUSH, $this->config['zmq_push_id']);

            //$this->pushSocket->setSockOpt(ZMQ::SOCKOPT_IPV4ONLY, 1);
            //$this->pushSocket->setSockOpt(ZMQ::SOCKOPT_IPV6, 1);
            //$retval = $this->pushSocket->getSockOpt(ZMQ::SOCKOPT_IPV6);

            // todo evtl ist timeout nicht mehr notwendig, wenn send() auch wieder bei einem HTTP_Request funktioniert und es kein Hänger gibt, auch wenn die WebSocket geschlosssen ist
            if (!empty($this->config['push_timeout'])) {
                $this->pushSocket->setSockOpt(ZMQ::SOCKOPT_LINGER, $this->config['push_timeout']); // timeout in ms
            }

            // weitere Options s. https://www.php.net/manual/de/class.zmq.php#zmq.constants.sockopt-linger

            $this->pushSocket->connect('tcp://127.0.0.1:' . $this->config['push_port']);
            //$connectedTo = $this->pushSocket->getEndpoints();
        }

        $this->pushSocket->send($message, ZMQ::MODE_DONTWAIT);
    }

    /**
     * Push a message to the web socket server.
     *
     * @param string $message The message to send.
     * @return bool
     */
    public function push2($message)
    {
        // todo Diese Funktion ist ein Workaround
        // Das folgende Problem tritt bei mir auf dem Windows-Laptop auf. Auf MAC, Linux oder Solidus-Server ist es noch nicht getestet.
        // - Wenn WebSocket::push() mittels WebSockerPushCommand ausgeführt wird, funktioniert alles.
        // - Wenn WebSocket::push() innerhalb eines HHT-Request ausgeführt wird, wird die Ausführung ohne Rückmeldung oder Exception bei $this->pushSocket->connect() beendet!!
        //   (per Browser http://localhost/laravel5/public/examples/websocket/push oder per AJAX-Request die Methode ChatController@ping aufrufen),

        $cmd = 'php artisan websocket:push ' . '"' . str_replace('"', '\\"', $message) . '"';

        // Achtung:
        // Zum Debuggen des Daemons sollte der xDebug-Helper im Browser besser deaktiviert werden! Ansonst horcht
        // PHPStorm auf zwei Ausführungen und blockiert sich u.U. selbst!!
        $env = null; //config('app.debug') ? ['XDEBUG_CONFIG' => 'idekey=PHPSTORM'] : null;

        /** @noinspection PhpParamsInspection */
        $process = new Process($cmd, base_path(), $env);
        $exitCode = $process->run();

        return $exitCode === 0;
    }
}