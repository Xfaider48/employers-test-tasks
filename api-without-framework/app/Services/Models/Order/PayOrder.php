<?php


namespace App\Services\Models\Order;


use App\Exceptions\ApiException;
use App\Models\Order;
use App\Models\OrderStatus;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Capsule\Manager;

class PayOrder implements PayOrderInterface
{
    /**
     * @param \App\Models\Order $order
     * @param float             $sum
     *
     * @return \App\Models\Order
     * @throws \Throwable
     */
    public function pay(Order $order, float $sum): Order
    {
        $connection = Manager::connection();
        return $connection->transaction(function () use ($order, $sum) {
            /** @var Order $forUpdateOrder */
            $forUpdateOrder = Order::query()->lockForUpdate()->find($order->id);
            $this->validateOrder($forUpdateOrder, $sum);
            $this->processOrder($forUpdateOrder);
            return $forUpdateOrder;
        });
    }

    /**
     * @param \App\Models\Order $order
     * @param float             $sum
     *
     * @throws \App\Exceptions\ApiException
     */
    protected function validateOrder(Order $order, float $sum): void
    {
        if (((float)$order->sum) !== $sum) {
            throw new ApiException("Bad `sum` value. You need to pay {$order->sum}" );
        }

        if ($order->order_status_id !== OrderStatus::ID_NEW) {
            throw new ApiException('Order already payed');
        }
    }

    /**
     * @param \App\Models\Order $order
     *
     * @throws \App\Exceptions\ApiException
     */
    protected function processOrder(Order $order): void
    {
        try {
            $guzzle = new Client();
            $response = $guzzle->get('ya.ru');
            if ($response->getStatusCode() !== 200) {
                throw new ApiException('Cant process order payment');
            }
        }
        catch (GuzzleException $e) {
            throw new ApiException('Cant process order payment');
        }

        $order->order_status_id = OrderStatus::ID_PAID;
        $order->save();
    }
}