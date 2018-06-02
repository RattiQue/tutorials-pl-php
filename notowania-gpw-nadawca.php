<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/shutdown.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

if (count(array_slice($argv, 1)) < 2) {
    file_put_contents(
        'php://stderr',
        "Korzystanie: php $argv[0] gpw.notowania.spolki.kghm \"Raport skonsolidowany za IV kwartał 2015 roku!\"\n"
    );
    exit(1);
}

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

$exchangeName = 'notowania-gpw';

$args = array_slice($argv, 1);
$key  = isset($args[0]) ? $args[0] : 'gpw.info.anonymous';

if (count($args) > 0) {
    $msg  = implode(' ', array_slice($args, 1));
} else {
    $msg  = 'Raport skonsolidowany za IV kwartał 2015 roku!';
}

$channel->exchange_declare($exchangeName, 'topic', false, false, false);

$message = new AMQPMessage($msg);

$channel->basic_publish($message, $exchangeName, $key);

echo ' [x] Wysłano ', $msg, "\n";

register_shutdown_function('shutdown', $channel, $connection);