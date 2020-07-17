<?php


namespace App\Services\Models\Order;


use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Collection;

class CreateOrder implements CreateOrderInterface
{
    /**
     * @param \App\Models\User               $user
     * @param \Illuminate\Support\Collection $products
     *
     * @return \App\Models\Order
     * @throws \Throwable
     */
    public function create(User $user, Collection $products): Order
    {
        $connection = Manager::connection();
        $sum = $products->pluck('price')->sum();

        return $connection->transaction(function () use ($user, $products, $sum) {
            $order = new Order();
            $order->user()->associate($user);
            $order->sum = $sum;
            $order->save();
            $order->products()->attach($products);
            return $order;
        });
    }
}