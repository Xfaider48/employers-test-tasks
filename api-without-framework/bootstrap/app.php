<?php

$app = new \App\Application(
    realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR)
);

return $app;