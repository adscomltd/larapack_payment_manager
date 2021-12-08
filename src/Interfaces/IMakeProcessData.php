<?php

namespace Adscom\LarapackPaymentManager\Interfaces;

use Adscom\LarapackPaymentManager\Contracts\PaymentCard;

interface IMakeProcessData
{
  public function getPaymentCard(): PaymentCard;

  public function getPostData(): array;
}
