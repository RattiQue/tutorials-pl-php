<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/shutdown.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

$numberOfAttributes = 3;

if (count(array_slice($argv, 1)) < $numberOfAttributes) {
    file_put_contents('php://stderr', "Korzystanie: php $argv[0] [imię] [płeć: k|m] [kolor włosów: blond|rude|siwe|czarne]\n");
    file_put_contents('php://stderr', "             php $argv[0] Karolina k blond\n");
    exit(1);
}

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

$exchangeName = 'randkomierz';

$arguments = array_slice($argv, 1);
$name = $arguments[0];
$attributes = array_slice($arguments, 1);
$headersList = ['plec', 'kolor-wlosow'];
$headersValues = [];

for ($i=0; $i<count($headersList); $i++) {
    $headerName  = $headersList[$i];
    $headerValue = $attributes[$i];

    $headersValues[$headerName] = $headerValue;
}

$channel->exchange_declare($exchangeName, 'headers', false, false, false);

$message = new AMQPMessage($name);
$headers = new AMQPTable($headersValues);

$message->set('application_headers', $headers);

$channel->basic_publish($message, $exchangeName, '');

echo ' [x] [x] Wysłano profil ', $name, "\n";

register_shutdown_function('shutdown', $channel, $connection);