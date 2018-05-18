<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/shutdown.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$text = implode(' ', array_slice($argv, 1));

if (empty($text)) {
    $text = "Witamy!";
}

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel    = $connection->channel();

$queue = 'task_queue';

$channel->queue_declare($queue, false, true, false, false);

$message = new AMQPMessage($text);

$channel->basic_publish($message, '', $queue);

echo " [x] Wysłano '$text'\n";

register_shutdown_function('shutdown', $channel, $connection);
