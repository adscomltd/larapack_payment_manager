<?php

namespace Adscom\LarapackPaymentManager\Contracts;

abstract class OrderAddress extends ModelContract
{
  public static function getContractClassName(): string
  {
    return self::class;
  }

  public function getCity(): string
  {
    return $this->model->city;
  }

  public function getCountryISO(): string
  {
    return $this->model->country->iso;
  }

  public function getAddressLine1(): string
  {
    return $this->model->address_line_1;
  }

  public function getAddressLine2(): ?string
  {
    return $this->model->address_line_2;
  }

  public function getZipCode(): string
  {
    return $this->model->zip_code;
  }

  public function getState(): ?string
  {
    return $this->model->state;
  }

  public function getName(): string
  {
    return $this->model->name;
  }

  public function getPhone(): string
  {
    return $this->model->phone;
  }
}
