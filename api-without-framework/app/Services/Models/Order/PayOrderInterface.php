<?php


namespace App\Services\Models\Order;


use App\Models\Order;

interface PayOrderInterface
{
    /**
     * @param \App\Models\Order $order
     * @param float             $sum
     *
     * @return \App\Models\Order
     */
    public function pay(Order $order, float $sum): Order;
}