<?php

use App\Application;

require __DIR__.'/vendor/autoload.php';

/**
 * @var \App\Application $app
 */
$app = require_once __DIR__.'/bootstrap/app.php';

require_once $app->basePath('database', 'Migration.php');

$migration = new Migration();
$migration->migrate();