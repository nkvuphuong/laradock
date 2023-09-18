<?php
require_once '../../../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'rabbitusr', 'rabbitpw');
    $channel = $connection->channel();

    $channel->queue_declare('greeting', false, false, false, false);

    $data = implode(' ', array_slice($argv, 1));
    if (empty($data)) {
        $data = 'Hi there: ' . date('c');
    }

    $msg = new AMQPMessage($data);

    $channel->basic_publish($msg, '', 'greeting');

    echo "Sent message: <<$data>>";
    $channel->close();
    $connection->close();
} catch (Exception $e) {
    dd($e->getMessage());
}
