<?php

namespace Adscom\LarapackPaymentManager\Interfaces;

use Adscom\LarapackPaymentManager\Contracts\PaymentAccount;
use Adscom\LarapackPaymentManager\Exceptions\PaymentDriverException;
use Exception;

interface IPaymentDriver
{
  /**
   * @param  array  $data
   * @throws PaymentDriverException
   * @throws Exception
   */
  public function processPayment(array $data = []): void;

  /**
   * @param  PaymentAccount  $paymentAccount
   */
  public function setup(PaymentAccount $paymentAccount): void;
}
