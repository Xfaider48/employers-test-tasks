<?php

use App\Application;

require __DIR__ . '/vendor/autoload.php';

$app = new Application(__DIR__);

$options = [
    'workerExchangeName' => $app->env('WORKER_EXCHANGE_NAME', 'ex-workers'),

    'number' => $app->env('WORKER_NUMBER'),
    'eventExchangeName' => $app->env('EVENT_EXCHANGE_NAME', 'ex-events'),

    'exchangeLogName' => $app->env('LOG_EXCHANGE_NAME', 'logs'),
    'queueLogName' => $app->env('LOG_QUEUE_NAME', 'task-log'),
];

try {
    $worker = new \App\Worker\Worker($app->db()->getConnection(), $app->ampq(), $options);
    $worker->init();
    $worker->start();
} finally {
    $app->ampq()->close();
}
