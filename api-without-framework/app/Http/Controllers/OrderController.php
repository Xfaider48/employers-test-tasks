<?php


namespace App\Http\Controllers;


use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class OrderController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Throwable
     */
    public function create(Request $request): JsonResponse
    {
        $jsonBody = json_decode($request->getContent(), true);
        if (!$jsonBody) {
            throw new BadRequestException('Malformed json body');
        }

        $productIds = Arr::get($jsonBody, 'product_ids');
        if (!$productIds) {
            throw new UnprocessableEntityHttpException('Field `product_ids` is required');
        }

        $productIds = new Collection($productIds);
        $validated = $productIds->map(function ($productId) {
            return (int)$productId;
        })->filter();

        if ($productIds->count() !== $validated->count()) {
            throw new UnprocessableEntityHttpException('Bad values in `product_ids`');
        }

        $inDbCount = Product::query()->whereIn('id', $validated)->count();
        if ($inDbCount !== $validated->count()) {
            throw new UnprocessableEntityHttpException('Bad values in `product_ids`');
        }

        $connection = Manager::connection();
        $order = $connection->transaction(function () use ($validated) {
            $order = new Order();
            $order->save();
            $order->products()->attach($validated);
            return $order;
        });

        $data = $order->id;
        return new JsonResponse(compact('data'));
    }

    /**
     * @param int $orderId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pay(int $orderId): Response
    {
        $order = Order::query()->find($orderId);
        if (!$order) {
            throw new NotFoundHttpException('Order not found');
        }

        $order->order_status_id = OrderStatus::ID_PAID;
        $order->save();
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}