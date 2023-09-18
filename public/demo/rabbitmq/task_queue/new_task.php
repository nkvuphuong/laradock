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

    for ($i = 0; $i < 100; $i++) {
        $data = 'Task No#' . $i;
        $msg = new AMQPMessage($data, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $channel->basic_publish($msg, '', $queueName);
        echo "Sent message: <<$data>> \n";
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    dd($e->getMessage());
}
