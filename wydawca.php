<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/shutdown.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

$exchangeName = 'empik';

$channel->exchange_declare($exchangeName, 'fanout', false, false, false);

$date  = new DateTime();
$issue = ((int) $date->format('n') + 1) . '/' . $date->format('Y');

$data = implode(' ', array_slice($argv, 1));

if (empty($data)) {
    $data = 'aktualne wydanie ' . $issue;
}

$message = new AMQPMessage($data);

$channel->basic_publish($message, $exchangeName);

echo " [x] Wysłano ", $data, "\n";

register_shutdown_function('shutdown', $channel, $connection);