<?php

namespace Adscom\LarapackPaymentManager\Interfaces;

use App\Models\PaymentCard;

interface ICreditCardPaymentDriver
{
  public function addPaymentMethod(PaymentCard $paymentCard): array;

  public function deletePaymentMethod(string $id): array;
}
