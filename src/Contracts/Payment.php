<?php

namespace Adscom\LarapackPaymentManager\Contracts;

use Adscom\LarapackPaymentManager\Drivers\PaymentDriver;

abstract class Payment extends ModelContract
{
  public static function getContractClassName(): string
  {
    return self::class;
  }

  public function getUuid(): string
  {
    return (string) $this->model->uuid;
  }

  public function getProcessorCurrency(): string
  {
    return $this->model->processor_currency;
  }

  public function getProcessorTransactionId(): string
  {
    return $this->model->processor_transaction_id;
  }

  public function getStatus(): string
  {
    return $this->model->status;
  }

  public static function create(array $data): static
  {
    return static::getContractRealizationClassName()::fromModel(
      static::getContractModelClassName()::create($data)
    );
  }

  abstract public static function getPaidStatus(): int;

  abstract public static function getCreatedStatus(): int;

  abstract public static function getInitiatedStatus(): int;

  abstract public static function getErrorStatus(): int;

  abstract public static function getRefundStatus(): int;

  abstract public static function getPartialRefundStatus(): int;

  abstract public static function getChargebackStatus(): int;

  abstract public static function getDeclinedStatus(): int;


  public function getOrder(): Order
  {
    return PaymentDriver::getOrderContractClass()::fromModel($this->model->order);
  }

  public function getAccount(): PaymentAccount
  {
    return PaymentDriver::getPaymentAccountContractClass()::fromModel($this->model->account);
  }

  public function getResponse(): array
  {
    return $this->model->response;
  }

  public static function findByUuid(string $uuid): ?static
  {
    return static::getContractRealizationClassName()::fromModel(
      static::getContractModelClassName()::where('uuid', $uuid)
        ->firstOrFail()
    );
  }

  public static function findByTransactionId(string $transactionId): ?static
  {
    return static::getContractRealizationClassName()::fromModel(
      static::getContractModelClassName()::where('processor_transaction_id', $transactionId)
        ->latest()
        ->firstOrFail()
    );
  }
}
