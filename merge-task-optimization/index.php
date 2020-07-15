<?php

require_once 'without-optimization.php';
require_once 'with-merge-optimization.php';

function execTest(Closure $exec, array &$input, int $numberOfTests = 5): array
{
    $execTimes = [];
    for ($i = 0; $i < $numberOfTests; $i++) {
        $start = microtime(true);
        $exec($input);
        $execTimes[] = microtime(true) - $start;
    }

    return [
        'min' => min($execTimes),
        'max' => max($execTimes),
        'avg' => array_sum($execTimes) / $numberOfTests
    ];
}

// Random input
$array = [];
for ($i = 0; $i < 1000; $i++) {
    $set = [];
    for ($j = 0; $j < 1000; $j++) {
        $set[] = rand(0, 1000);
    }

    $array[] = $set;
}

// Test without optimization
$resultOne = execTest(function (array &$input) {
    return resolveWithoutOptimization($input);
}, $array);
echo 'Without optimization result: ' . PHP_EOL;
echo 'Min: ' . $resultOne['min'] . PHP_EOL;
echo 'Max: ' . $resultOne['max'] . PHP_EOL;
echo 'Avg: ' . $resultOne['avg'] . PHP_EOL;
echo PHP_EOL;

// Test with optimization
$resultTwo = execTest(function (array &$input) {
    return resolveWithMergeOptimization($input);
}, $array);
echo 'With optimization result: ' . PHP_EOL;
echo 'Min: ' . $resultTwo['min'] . PHP_EOL;
echo 'Max: ' . $resultTwo['max'] . PHP_EOL;
echo 'Avg: ' . $resultTwo['avg'] . PHP_EOL;
echo PHP_EOL;