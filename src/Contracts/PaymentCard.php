<?php

namespace Adscom\LarapackPaymentManager\Contracts;

abstract class PaymentCard extends ModelContract
{
  public static function getContractClassName(): string
  {
    return self::class;
  }

  public function getUuid(): string
  {
    return (string) $this->model->uuid;
  }

  public function getNumber(): string
  {
    return $this->model->number;
  }

  public function getExpirationMonth(): string
  {
    return $this->model->exp_month;
  }

  public function getExpirationYear(): string
  {
    return $this->model->exp_year;
  }

  public function getCVC(): string
  {
    return $this->model->cvc;
  }

  public function getBillingDetails(): array
  {
    return $this->model->billing_address;
  }

  public function getName(): string
  {
    return $this->getBillingDetails()['name'];
  }

  public function getCity(): string
  {
    return $this->getBillingDetails()['city'];
  }

  abstract public function getCountryISO(): string;

  public function getAddressLine1(): string
  {
    return $this->getBillingDetails()['address_line_1'];
  }

  public function getAddressLine2(): ?string
  {
    return $this->getBillingDetails()['address_line_2'];
  }

  public function getZipCode(): string
  {
    return $this->getBillingDetails()['zip_code'];
  }

  public function getState(): ?string
  {
    return $this->getBillingDetails()['state'];
  }
}
