<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    $queueName = 'task_queue';
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'rabbitusr', 'rabbitpw');
    $channel = $connection->channel();

    $durable = true; //make sure that the queue will survive a RabbitMQ node restart
    $channel->queue_declare($queueName, false, $durable, false, false);

    echo " [*] Waiting for messages. To exit press CTRL+C\n";

    $callback = static function (PhpAmqpLib\Message\AMQPMessage $msg) {
        $sleepingTime = rand(1, 5);
        echo " [x] Received: waiting for $sleepingTime seconds ", $msg->body, "\n";
        sleep($sleepingTime);
        echo " [x] Done: " . date('c') . "\n";
        $msg->ack();
    };

    $channel->basic_qos(null, 1, null); // (Fair dispatch) This tells RabbitMQ not to give more than one message to a worker at a time. Or, in other words, don't dispatch a new message to a worker until it has processed and acknowledged the previous one. Instead, it will dispatch it to the next worker that is not still busy.

    $channel->basic_consume($queueName, '', false, false, false, false, $callback);

    while ($channel->is_open()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    dd($e->getMessage());
}
