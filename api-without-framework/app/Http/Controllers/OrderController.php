<?php


namespace App\Http\Controllers;


use App\Application;
use App\Exceptions\ApiException;
use App\Models\Order;
use App\Models\Product;
use App\Services\Models\Order\CreateOrderInterface;
use App\Services\Models\Order\PayOrderInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class OrderController extends AbstractController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Throwable
     */
    public function create(Request $request): JsonResponse
    {
        // Validate input
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

        $products = Product::query()->whereIn('id', $validated)->get();
        if ($products->count() !== $validated->count()) {
            throw new UnprocessableEntityHttpException('Bad values in `product_ids`');
        }

        try {
            /** @var CreateOrderInterface $creator */
            $creator = Application::getInstance()->get(CreateOrderInterface::class);
            $order = $creator->create($this->user, $products);
        }
        catch (ApiException $e) {
            throw $e->toHttpException();
        }

        $data = $order->id;
        return new JsonResponse(compact('data'));
    }

    /**
     * @param int                                       $orderId
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
     * @throws \Throwable
     */
    public function pay(int $orderId, Request $request): Response
    {
        /** @var Order $order */
        $order = Order::query()
            ->where('user_id', $this->user->id)
            ->find($orderId);

        if (!$order) {
            throw new NotFoundHttpException('Order not found');
        }

        // Validate input
        $jsonBody = json_decode($request->getContent(), true);
        if (!$jsonBody) {
            throw new BadRequestException('Malformed json body');
        }

        $sum = (float) Arr::get($jsonBody, 'sum');
        if ($sum <= 0) {
            throw new UnprocessableEntityHttpException('Bad `sum` value');
        }

        try {
            /** @var PayOrderInterface $payer */
            $payer = Application::getInstance()->get(PayOrderInterface::class);
            $payer->pay($order, $sum);
        } catch (ApiException $e) {
            throw $e->toHttpException();
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}