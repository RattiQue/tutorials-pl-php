<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/shutdown.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

$routingKeys = array_slice($argv, 1);

if (empty($routingKeys)) {
    file_put_contents('php://stderr', "Korzystanie: $argv[0] gpw.notowania.spolki.kghm\n");
    file_put_contents('php://stderr', "             $argv[0] gpw.new-connect.*\n");
    file_put_contents('php://stderr', "             $argv[0] gpw.#\n");
    file_put_contents('php://stderr', "             $argv[0] gpw.notowania.spolki.kghm gpw.notowania.spolki.orlen\n");

    exit(1);
}

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

$exchangeName = 'notowania-gpw';

$channel->exchange_declare($exchangeName, 'topic', false, false, false);

list($queueName, ,) = $channel->queue_declare('', false, false, true, false);

foreach ($routingKeys as $routingKey) {
    $channel->queue_bind($queueName, $exchangeName, $routingKey);
}

echo sprintf(' [*] Oczekiwanie na wiadomości w %s.', $queueName), "\n";
echo '     Naciśnij CTRL+C aby zakończyć.', "\n";

$channel->basic_consume($queueName, '', false, true, false, false, function($message) {
    echo ' [x] ' . $message->delivery_info['routing_key'] . ': ' . $message->body . "\n";
});

register_shutdown_function('shutdown', $channel, $connection);

while(count($channel->callbacks)) {
    $channel->wait();
}