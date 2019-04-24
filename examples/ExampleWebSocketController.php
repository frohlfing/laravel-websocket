<?php

namespace App\Sockets;

use FRohlfing\WebSocket\BaseWebSocketController;
use stdClass;

class ExampleWebSocketController extends BaseWebSocketController {

	public function onOpen($conn)
	{
        /** @var \Ratchet\WebSocket\Version\RFC6455\Connection $conn */
        /** @noinspection PhpUndefinedFieldInspection */
        $conn->client = new stdClass;
        /** @noinspection PhpUndefinedFieldInspection */
        $conn->client->id  = uniqid();
        /** @noinspection PhpUndefinedFieldInspection */
        echo $conn->client->id . ': Connection ' . $conn->resourceId . ' open' . "\n";
        $conn->send(json_encode(['message' => 'Welcome!']));
	}

	public function onClose($conn)
	{
        /** @var \Ratchet\WebSocket\Version\RFC6455\Connection $conn */
        /** @noinspection PhpUndefinedFieldInspection */
        echo $conn->client->id . ': Connection ' . $conn->resourceId . ' close' . "\n";
	}

    public function onMessage($from, $msg)
    {
        /** @var \Ratchet\WebSocket\Version\RFC6455\Connection $from */
        $data = json_decode($msg, true);
        /** @noinspection PhpUndefinedFieldInspection */
        echo $from->client->id . ': Client send message "' . $data['message'] . '"' . "\n";
        $this->broadcastExclude(json_encode($data), [$from]);
    }

    public function onPush($msg)
    {
        $data = json_decode($msg, true);
        echo '0000000000000: Webserver send message "' . $data['message'] . '"' . "\n";
        $this->broadcast(json_encode($data));
    }

	public function onError($conn, \Exception $e)
	{
        /** @var \Ratchet\WebSocket\Version\RFC6455\Connection $conn */
        /** @noinspection PhpUndefinedFieldInspection */
        echo $conn->client->id . ': Exception: ' . $e . "\n";
        $conn->close();
		//throw new Exception($exception);
	}

}
