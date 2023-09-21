<?php

use PhpAmqpLib\Message\AMQPMessage;

require 'init.php';

$queueConnection = new rabbit_queue();
$channel = $queueConnection->getChannel();
$routingKey = 'order_queue';
echo '[*] Declaring queue' . PHP_EOL;
$channel->queue_declare($routingKey, false, true, false, false);

try {
    $designs = json_decode(file_get_contents("https://picsum.photos/v2/list?page=2&limit=100"), 1, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    dd($e->getMessage());
}
$faker = Faker\Factory::create();

for ($i = 0; $i < 100; $i++) {

    $order = [
        'uuid' => uuid_create(),
        'seller_id' => rand(1, 1000),
        'date' => date('c'),
        'shipping_to' => [
            'name' => $faker->name,
            'email' => $faker->email,
            'address' => $faker->address,
            'phone' => $faker->phoneNumber,
            'country_code' => $faker->countryCode,
            'zip_code' => $faker->postcode
        ]
    ];

    $order['items'] = [];

    for ($j = 1; $j <= rand(1, 10); $j++) {
        $order['items'][] = [
            'product_id' => rand(1, 5),
            'quantity' => rand(1, 10),
            'design_url' => $designs[rand(0, 99)]['download_url']
        ];
    }

    try {
        $msg = new AMQPMessage(json_encode($order, JSON_THROW_ON_ERROR), array('content_type' => 'application/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
    } catch (JsonException $e) {
        dd($e->getMessage());
    }
    $channel->basic_publish($msg, '', $routingKey);
    echo "[+] Pushed order: #{$order['uuid']}" . PHP_EOL;
}


