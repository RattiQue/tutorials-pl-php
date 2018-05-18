<?php

declare(ticks = 1); // enable signal handling

function sigint()  {
    exit;
}

pcntl_signal(SIGINT, 'sigint');
pcntl_signal(SIGTERM, 'sigint');

/**
 * Executes when application close
 *
 * @param \PhpAmqpLib\Channel\AMQPChannel $channel
 * @param \PhpAmqpLib\Connection\AbstractConnection $connection
 */
function shutdown($channel, $connection)
{
    $channel->close();
    $connection->close();

    echo PHP_EOL, 'Aktywne połączenia zostały pomyślnie zakończone.', PHP_EOL;
}
