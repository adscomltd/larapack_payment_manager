<?php

namespace Adscom\LarapackPaymentManager\Contracts;

use Adscom\LarapackPaymentManager\Drivers\PaymentDriver;
use Illuminate\Support\Collection;

abstract class Order extends ModelContract
{
  public static function getContractClassName(): string
  {
    return self::class;
  }

  public function getUuid(): string
  {
    return (string) $this->model->uuid;
  }

  public function getDueAmount(): float
  {
    return $this->model->due_amount;
  }

  public function getDueAmountWithoutShipping(): float
  {
    return $this->model->due_amount_without_shipping;
  }

  public function getProcessorCurrency(): string
  {
    return $this->model->processor_currency;
  }

  abstract public function hasShippingData(): bool;

  public function getShippingName(): string
  {
    return $this->model->shipping_data['method_name'];
  }

  public function getShippingCost(): float
  {
    return $this->getDueAmount() - $this->getDueAmountWithoutShipping();
  }

  public function getPayments(): Collection
  {
    return $this->model->payments->map(fn($model) => PaymentDriver::getPaymentContractClass()::fromModel($model));
  }

  public function getLineItems(): Collection
  {
    return $this->model->lineItems->map(fn($model) => PaymentDriver::getOrderItemContractClass()::fromModel($model));
  }

  public function getAddress(): OrderAddress
  {
    return PaymentDriver::getOrderAddressContractClass()::fromModel($this->model->shippingAddress);
  }
}
