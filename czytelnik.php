<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/shutdown2.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel    = $connection->channel();

$exchangeName = 'empik';

$channel->exchange_declare($exchangeName, 'fanout', false, false, false);

list($queueName, ,) = $channel->queue_declare('', false, false, true, false);

$channel->queue_bind($queueName, $exchangeName);

echo sprintf(' [*] Oczekiwanie na wiadomości w %s.', $queueName), "\n";
echo '     Naciśnij CTRL+C aby zakończyć.', "\n";

$callback = function($message) {
    echo " [x] Odebrano ", $message->body, "\n";
    echo " [x] Zrobione", "\n";
};

$channel->basic_consume(
    $queueName,
    '',
    false,
    true,
    false,
    false,
    $callback
);

register_shutdown_function('shutdown', $channel, $connection);

while(count($channel->callbacks)) {
    $channel->wait();
}