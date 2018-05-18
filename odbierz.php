<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/shutdown.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel    = $connection->channel();

$queue = 'hello';

$channel->queue_declare($queue, false, false, false, false);

echo sprintf(' [*] Oczekiwanie na wiadomości w %s.', $queue), "\n";
echo '     Naciśnij CTRL+C aby zakończyć.', "\n";

$channel->basic_consume(
    $queue,
    '',
    false,
    true,
    false,
    false,
    function($message) {
        echo " [x] Odebrano ", $message->body, "\n";
    }
);

register_shutdown_function('shutdown', $channel, $connection);

while(count($channel->callbacks)) {
    $channel->wait();
}