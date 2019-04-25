<?php

namespace FRohlfing\WebSocket\Console\Commands;

use FRohlfing\WebSocket\Facades\WebSocket;
use Illuminate\Console\Command;

class WebSocketPushCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:push
                            {message : Message to push}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push a message to the web socket.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        WebSocket::push($this->argument('message'));

        $this->line('Message sent');
    }
}
