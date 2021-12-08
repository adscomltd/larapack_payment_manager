<?php

namespace Adscom\LarapackPaymentManager\Contracts;

abstract class OrderItem extends ModelContract
{
  public static function getContractClassName(): string
  {
    return self::class;
  }

  abstract public function getName(): string;

  abstract public function getSKU(): ?string;

  public function getPrice(): float
  {
    return $this->model->price;
  }

  public function getQuantity(): float
  {
    return $this->model->qty;
  }
}
