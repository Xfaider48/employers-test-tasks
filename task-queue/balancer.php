<?php

use App\Application;

require __DIR__ . '/vendor/autoload.php';

$app = new Application(__DIR__);

$options = [
    'eventQueueName' => $app->env('EVENT_QUEUE_NAME', 'events'),
    'eventExchangeName' => $app->env('EVENT_EXCHANGE_NAME', 'ex-events'),
    'workerExchangeName' => $app->env('WORKER_EXCHANGE_NAME', 'ex-workers'),
    'numberOfWorkers' => $app->env('NUMBER_OF_WORKERS', 1),

    'exchangeLogName' => $app->env('LOG_EXCHANGE_NAME', 'logs'),
    'queueLogName' => $app->env('LOG_QUEUE_NAME', 'task-log'),
];

try {
    $worker = new \App\Balancer\Balancer($app->db()->getConnection(), $app->ampq(), $options);
    $worker->init();
    $worker->start();
} finally {
    $app->ampq()->close();
}
