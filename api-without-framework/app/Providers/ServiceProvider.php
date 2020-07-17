<?php


namespace App\Providers;


use App\Services\Models\Order\CreateOrder;
use App\Services\Models\Order\CreateOrderInterface;
use App\Services\Models\Order\PayOrder;
use App\Services\Models\Order\PayOrderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServiceProvider
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function provide(ContainerBuilder $container)
    {
        $container->register(CreateOrderInterface::class, CreateOrder::class);
        $container->register(PayOrderInterface::class, PayOrder::class);
    }
}