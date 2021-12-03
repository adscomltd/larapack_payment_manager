<?php

namespace Adscom\LarapackPaymentManager\Facades;

use Illuminate\Support\Facades\Facade;

class PaymentManager extends Facade
{
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor(): string
  {
    return 'PaymentManager';
  }
}
