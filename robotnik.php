<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/shutdown2.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel    = $connection->channel();

$queue = 'task_queue';

$channel->queue_declare($queue, false, true, false, false);

echo sprintf(' [*] Oczekiwanie na wiadomości w %s.', $queue), "\n";
echo '     Naciśnij CTRL+C aby zakończyć.', "\n";

$callback = function($message) {
    echo " [x] Odebrano ", $message->body, "\n";

    $secs = substr_count($message->body, '.');

    sleep($secs);

    echo " [x] Zrobione", "\n";
};

$channel->basic_consume(
    $queue,
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