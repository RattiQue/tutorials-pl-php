<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/shutdown.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

$exchangeName = 'randkomierz';

$channel->exchange_declare($exchangeName, 'headers', false, false, false);

list($queueName, ,) = $channel->queue_declare('', false, false, true, false);

$channel->queue_bind($queueName, $exchangeName, '', false, new AMQPTable([
    'x-match' => 'all',
    'plec' => 'k',
    'kolor-wlosow' => 'czarne'
]));

echo sprintf(' [*] Oczekiwanie na wiadomości w %s.', $queueName), "\n";
echo '     Naciśnij CTRL+C aby zakończyć.', "\n";

$channel->basic_consume($queueName, '', false, true, false, false, function($message) {
    echo ' [x] ' . $message->body . ' pasuje do Ciebie' . "\n";
});

register_shutdown_function('shutdown', $channel, $connection);

while(count($channel->callbacks)) {
    $channel->wait();
}