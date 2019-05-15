<?php

namespace FRohlfing\WebSocket\Console\Commands;

use Exception;
use FRohlfing\WebSocket\Contracts\WebSocketHandler;
use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\Server;
use React\ZMQ\Context;

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
        $this->info('Start Web Socket Server...');

        try {
            $port = $this->option('port');
            //if ($port === null) {
            //    $port = config('websocket.port');
            //}
            $pushPort = config('websocket.push_port');

            // Create an event loop
            $loop = Factory::create();

            // get the web socket handler
            $handler = app(WebSocketHandler::class);

            // Listen for the web server message to redirect to the web socket
            $context = new Context($loop);
            /** @var \React\ZMQ\SocketWrapper $pull */
            /** @noinspection PhpUndefinedMethodInspection */
            $pull = $context->getSocket(\ZMQ::SOCKET_PULL, config('websocket.zmq_pull_id'));
            /** @noinspection PhpUndefinedMethodInspection */
            $pull->bind('tcp://127.0.0.1:' . $pushPort); // Binding to 127.0.0.1 means the only client that can connect is itself
            $pull->on('message', [$handler, 'onPush']);

            // Set up our web socket server for clients wanting real-time updates
            $webSock = new Server('0.0.0.0:' . $port, $loop); // Binding to 0.0.0.0 means remotes can connect
            new IoServer(
                new HttpServer(
                    new WsServer(
                        $handler
                    )
                ), $webSock
            );

            $this->info('Web socket is listening on port ' . $port);
            $this->info('Push service is listening on port ' . $pushPort);

            $loop->run();
        }
        catch (Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }
            $this->error($e->getMessage());
            return static::EXIT_FAILURE;
        }

        $this->info('Web Socket Server stopped.');

        return static::EXIT_SUCCESS;
    }
}
