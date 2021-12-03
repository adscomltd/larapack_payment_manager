<?php

namespace Adscom\LarapackPaymentManager\Interfaces;

use App\Models\PaymentCard;

interface IMakeProcessData
{
  public function getPaymentCard(): PaymentCard;

  public function getPostData(): array;
}
