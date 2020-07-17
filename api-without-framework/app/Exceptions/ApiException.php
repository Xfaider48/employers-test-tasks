<?php


namespace App\Exceptions;


use Exception;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ApiException extends Exception
{
    /**
     * @return \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
     */
    public function toHttpException(): HttpExceptionInterface
    {
        return new UnprocessableEntityHttpException($this->message);
    }
}