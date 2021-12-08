<?php

namespace Adscom\LarapackPaymentManager\Interfaces;

use Adscom\LarapackPaymentManager\Contracts\PaymentCard;
use Adscom\LarapackPaymentManager\Contracts\PaymentToken;

interface ITokenable
{
  public function getOrCreateToken(PaymentCard $paymentCard): PaymentToken;
}
