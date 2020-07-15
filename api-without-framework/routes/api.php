<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Symfony\Component\Routing\Route;

/** @var \Symfony\Component\Routing\RouteCollection $routes */
$routes->add('ProductController:generate', new Route('/products/generate', [
    '_controller' => [ProductController::class, 'generate']
], [], [], '', [], ['GET']
));

$routes->add('OrderController:create', new Route('/orders', [
    '_controller' => [OrderController::class, 'create']
], [], [], '', [], ['POST']
));

$routes->add('OrderController:pay', new Route('/orders/{orderId}/pay', [
    '_controller' => [OrderController::class, 'pay']
], [
    'orderId' => '\d+'
], [], '', [], ['POST']
));

