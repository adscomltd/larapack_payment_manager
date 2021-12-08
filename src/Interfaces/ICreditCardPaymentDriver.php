<?php

namespace Adscom\LarapackPaymentManager\Interfaces;

use Adscom\LarapackPaymentManager\Contracts\PaymentCard;

interface ICreditCardPaymentDriver
{
  public function addCard(PaymentCard $paymentCard): array;

  public function deletePaymentMethod(string $id): array;
}
