<?php


namespace App\Http\Controllers;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Controller extends AbstractController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function exception(Request $request): JsonResponse
    {
        /** @var \Exception $exception */
        $exception = $request->get('exception');
        return new JsonResponse($this->exceptionToArray($exception));
    }

    /**
     * @param \Throwable $throwable
     *
     * @return array
     */
    protected function exceptionToArray(\Throwable $throwable): array
    {
        if ($throwable instanceof HttpExceptionInterface) {
            $message = $throwable->getMessage();
        } else {
            $message = 'Internal server error';
        }

        return [
            'message' => $message,
            'code' => $throwable->getCode()
        ];
    }
}