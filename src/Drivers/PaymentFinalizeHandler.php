<?php

namespace Adscom\LarapackPaymentManager\Drivers;

use Adscom\LarapackPaymentManager\Interfaces\IFinalizeHandler;

class PaymentFinalizeHandler implements IFinalizeHandler
{
  public function __construct(protected PaymentDriver $driver)
  {
  }

  public function process(array $data): array
  {
    return $data;
  }
}
