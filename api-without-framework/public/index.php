<?php

require __DIR__.'/../vendor/autoload.php';

/**
 * @var \App\Application $app
 */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handle(
    \Symfony\Component\HttpFoundation\Request::createFromGlobals()
);

