<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/shutdown.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

$queue = 'hello';

$channel->queue_declare($queue = 'hello', false, false, false, false);

$text = 'Witamy!';

$message = new AMQPMessage($text);

$channel->basic_publish($message, '', $queue);

echo " [x] Wysłano '$text'\n";


register_shutdown_function('shutdown', $channel, $connection);
