<?php
require_once '../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'rabbitusr', 'rabbitpw');
    $channel = $connection->channel();

    $channel->queue_declare('greeting', false, false, false, false);

    echo " [*] Waiting for messages. To exit press CTRL+C\n";

    $callback = function (PhpAmqpLib\Message\AMQPMessage $msg) {
        echo ' [x] Received ', $msg->body, "\n";
        sleep(substr_count($msg->body, '.'));
        echo " [x] Done\n";
        $msg->ack();
    };

    $channel->basic_consume('greeting', '', false, false, false, false, $callback);

    while ($channel->is_open()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    dd($e->getMessage());
}
