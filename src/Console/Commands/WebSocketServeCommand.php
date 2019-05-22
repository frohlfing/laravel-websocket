<?php

namespace FRohlfing\WebSocket\Console\Commands;

use App\Exceptions\Handler;
use Exception;
use FRohlfing\WebSocket\Contracts\WebSocketHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\Server;
use React\ZMQ\Context;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to start the web socket server.
 *
 * @see http://socketo.me/docs/hello-world Ratchet Tutorial
 * @see http://socketo.me/docs/push Push Integration by Ratchet
 */

class WebSocketServeCommand extends Command
{
    /**
     * Exit Codes.
     */
    const EXIT_SUCCESS = 0;
    const EXIT_FAILURE = 1;

    /**
     * The name and signature of the console command.
     *
     * Inherited options:
     *   -h, --help            Display this help message
     *   -q, --quiet           Do not output any message
     *   -V, --version         Display this application version
     *       --ansi            Force ANSI output
     *       --no-ansi         Disable ANSI output
     *   -n, --no-interaction  Do not ask any interactive question
     *       --env[=ENV]       The environment the command should run under
     *   -v|vv|vvv, --verbose  Increase the verbosity of messages
     *
     * @var string
     */
    protected $signature = 'websocket:serve
                            { --p|port=%1 : The Port on which we listen for new connections }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a web socket server.';

    /**
     * Web Socket Handler
     *
     * @var WebSocketHandler
     */
    private $handler;

    /**
     * React Event Loop instance
     *
     * @var LoopInterface
     */
    private $loop;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        $this->signature = str_replace('%1', config('websocket.port'), $this->signature);

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \ZMQSocketException
     */
    public function handle()
    {
        if ($this->getOutput()->getVerbosity() === OutputInterface::VERBOSITY_NORMAL) {
            switch (strtolower(config('websocket.logLevel', 'error'))) {
                case 'none': // argument -q
                    $this->getOutput()->setVerbosity(OutputInterface::VERBOSITY_QUIET);
                    break;
                case 'error':
                    // $this->getOutput()->setVerbosity(OutputInterface::VERBOSITY_NORMAL); // is already set
                    break;
                case 'info':  // argument -v or -vv
                    $this->getOutput()->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
                    break;
                case 'debug': // argument -vvv
                    $this->getOutput()->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
                    break;
            }
        }

        $this->logInfo('Start Web Socket Server...');

        try {
            $port = $this->option('port');
            //if ($port === null) {
            //    $port = config('websocket.port');
            //}
            $pushPort = config('websocket.push_port');

            // Create an event loop
            $this->loop = Factory::create();

            // Get the web socket handler
            $this->handler = app(WebSocketHandler::class, [$this]);

            // Listen for the web server message to redirect to the web socket
            $context = new Context($this->loop);
            /** @var \React\ZMQ\SocketWrapper $pull */
            /** @noinspection PhpUndefinedMethodInspection */
            $pull = $context->getSocket(\ZMQ::SOCKET_PULL, config('websocket.zmq_pull_id'));
            /** @noinspection PhpUndefinedMethodInspection */
            $pull->bind('tcp://127.0.0.1:' . $pushPort); // Binding to 127.0.0.1 means the only client that can connect is itself
            $pull->on('message', [$this, 'onPush']);

            // Set up our web socket server for clients wanting real-time updates
            $webSock = new Server('0.0.0.0:' . $port, $this->loop); // Binding to 0.0.0.0 means remotes can connect
            new IoServer(
                new HttpServer(
                    new WsServer(
                        $this->handler
                    )
                ), $webSock
            );

            $this->logInfo('Web socket is listening on port ' . $port);
            $this->logInfo('Push service is listening on port ' . $pushPort);

            $this->loop->run();
        }
        catch (Exception $e) {
            $errorMessage = get_class($e) . ' in "' . $e->getFile() . '", line ' . $e->getLine() . ': ' . $e->getMessage() . ' (code ' . $e->getCode() . ')';
            $this->logError($errorMessage);
            return static::EXIT_FAILURE;
        }

        $this->logInfo('Web Socket Server stopped.');

        return static::EXIT_SUCCESS;
    }

    /**
     * Received a push message.
     *
     * @param string $msg
     */
    public function onPush($msg)
    {
        try {
            $this->handler->onPush($msg);
        }
        catch (Exception $e) {
            $this->handler->onPushError($e);
        }
    }

    /**
     * Set one-off timer.
     *
     * @param float $seconds The interval after which this timer will execute, in seconds
     * @param callable $callback The callback that will be executed when this timer elapses
     * @return React\EventLoop\Timer
     */
    public function setTimeout($seconds, $callback)
    {
        return $this->loop->addTimer($seconds, $callback);
    }

    /**
     * Set timer at a set interval.
     *
     * @param float $seconds The interval after which this timer will execute, in seconds
     * @param callable $callback The callback that will be executed when this timer elapses
     * @return React\EventLoop\Timer
     */
    public function setInterval($interval, $callback)
    {
        return $this->loop->addPeriodicTimer($interval, $callback);
    }

    /**
     * Stops a running timer.
     *
     * @param float $seconds The interval after which this timer will execute, in seconds
     * @param callable $callback The callback that will be executed when this timer elapses
     * @return React\EventLoop\Timer
     */
    public function cancelTimer(TimerInterface $timer)
    {
        $this->loop->cancelTimer($timer);
    }

    /**
     * Log an error message if the log level is not configured as "none".
     *
     * @param string $msg
     */
    public function logError($msg)
    {
        if ($this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            Log::error($msg);
            $this->error($msg);
        }
    }

    /**
     * Log an information if the log level is configured as "info" or "debug".
     *
     * @param string $msg
     */
    public function logInfo($msg)
    {
        if ($this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            Log::info($msg);
            $this->info($msg);
        }
    }

    /**
     * Log an debug message if the log level is configured as "debug".
     *
     * @param string $msg
     */
    public function logDebug($msg)
    {
        if ($this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            Log::debug($msg);
            $this->line($msg);
        }
    }
}
