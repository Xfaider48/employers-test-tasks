<?php


namespace App\Services\Models\Order;


use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

interface CreateOrderInterface
{
    /**
     * @param \App\Models\User               $user
     * @param \Illuminate\Support\Collection $products
     *
     * @return \App\Models\Order
     */
    public function create(User $user, Collection $products): Order;
}