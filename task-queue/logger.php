<?php

use App\Application;
use App\Logger\Logger;

require __DIR__ . '/vendor/autoload.php';

$app = new Application(__DIR__);

$options = [
    'queueLogName' => $app->env('QUEUE_LOG_NAME', 'task-log'),
];

try {
    $logger = new Logger($app->ampq(), $options);
    $logger->init();
    $logger->start();
} finally {
    $app->ampq()->close();
}
