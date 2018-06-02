<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/shutdown.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

$exchangeName = 'direct_logs';

$severity = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'info';

$data = implode(' ', array_slice($argv, 2));

if (empty($data)) {
    $data = 'Witajcie!';
}

$channel->exchange_declare($exchangeName, 'direct', false, false, false);

$message = new AMQPMessage($data);

$channel->basic_publish($message, $exchangeName, $severity);

echo ' [x] Wysłano ', $data, "\n";

register_shutdown_function('shutdown', $channel, $connection);