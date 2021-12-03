<?php

namespace Adscom\LarapackPaymentManager\Exceptions;

use Exception;

class PaymentRedirectionException extends Exception
{
  public function __construct(public string $url, public array $response = [],
    public string $reason = 'payment_redirection_exception',
    public array $notes = [])
  {
    parent::__construct("Redirect to {$url}");
  }
}
