<?php

namespace Adscom\LarapackPaymentManager\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;

class PaymentDriverException extends Exception
{
  protected array $throwable = [
    ValidationException::class,
  ];

  /**
   * @throws Exception
   */
  public static function fromException(Exception $exception): self
  {
    $instance = new self($exception->getMessage(), $exception->getCode(), $exception);

    foreach ($instance->throwable as $exceptionClass) {
      if ($exception instanceof $exceptionClass) {
        throw $exception;
      }
    }

    return $instance;
  }
}
