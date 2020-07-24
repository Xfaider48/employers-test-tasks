<?php

use App\Application;
use App\Publisher\Producer;

require __DIR__ . '/vendor/autoload.php';

$app = new Application(__DIR__);

$options = [
    'eventQueueName' => $app->env('EVENT_QUEUE_NAME', 'events'),
    'eventExchangeName' => $app->env('EVENT_EXCHANGE_NAME', 'ex-events'),

    'exchangeLogName' => $app->env('LOG_EXCHANGE_NAME', 'logs'),
    'queueLogName' => $app->env('LOG_QUEUE_NAME', 'task-log'),
];

$publisher = new Producer($app->ampq(), $options);
$publisher->init();

$numberOfGenerations = (int) $app->env('NUMBER_OF_GENERATIONS', 10);
$maxNumberOfClients = (int) $app->env('NUMBER_OF_CLIENTS', 10);
$maxNumberOfEvents = (int) $app->env('NUMBER_OF_CLIENT_EVENTS', 100);
$channels = explode(',', $app->env('CHANNELS', 'telegram,viber,facebook'));
$channelsCount = count($channels) - 1;

$payload = 0;
// Generate group
for ($i = 0; $i < $numberOfGenerations; $i++) {
    $clientId = rand(1, $maxNumberOfClients);
    $numberOfEvents = rand(1, $maxNumberOfEvents);
    for ($j = 0; $j < $numberOfEvents; $j++) {
        $channel = $channels[rand(0, $channelsCount)];
        $event = compact('channel', 'clientId', 'payload');
        $payload++;
        $publisher->publish($event);
    }
}

$app->ampq()->close();
