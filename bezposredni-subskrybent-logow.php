<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/shutdown.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

$exchangeName = 'direct_logs';

$channel->exchange_declare($exchangeName, 'direct', false, false, false);

list($queueName, ,) = $channel->queue_declare('', false, false, true, false);

$severities = array_slice($argv, 1);

if (empty($severities)) {
    file_put_contents('php://stderr', "Korzystanie: $argv[0] [info] [warning] [error]\n");
    exit(1);
}

foreach ($severities as $severity) {
    $channel->queue_bind($queueName, $exchangeName, $severity);
}

echo sprintf(' [*] Oczekiwanie na wiadomości w %s.', $queueName), "\n";
echo '     Naciśnij CTRL+C aby zakończyć.', "\n";

$channel->basic_consume($queueName, '', false, true, false, false, function($msg) {
    echo ' [x] ' . $msg->delivery_info['routing_key'] . ': ' . $msg->body . "\n";
});

register_shutdown_function('shutdown', $channel, $connection);

while(count($channel->callbacks)) {
    $channel->wait();
}