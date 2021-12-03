<?php

namespace Adscom\LarapackPaymentManager\Interfaces;

use App\Models\PaymentCard;
use App\Models\PaymentToken;

interface ITokenable
{
  public function getOrCreateToken(PaymentCard $paymentCard): PaymentToken;
}
