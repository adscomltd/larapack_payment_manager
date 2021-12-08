<?php

namespace Adscom\LarapackPaymentManager\Contracts;

use Adscom\LarapackPaymentManager\Drivers\PaymentDriver;

abstract class PaymentAccount extends ModelContract
{
  public static function getContractClassName(): string
  {
    return self::class;
  }

  public function getUuid(): string
  {
    return (string) $this->model->uuid;
  }

  public function getId(): int
  {
    return $this->model->id;
  }

  public function getDescriptor(): string
  {
    return $this->model->descriptor;
  }

  public function getConfig(): array
  {
    return $this->model->config ?? [];
  }

  abstract public function getData(): array;

  public function getProcessor(): Processor
  {
    return PaymentDriver::getProcessorContractClass()::fromModel($this->model->processor);
  }

  public function getCompany(): Company
  {
    return PaymentDriver::getCompanyContractClass()::fromModel($this->model->company);
  }

  abstract public function createData(array $data): void;

  public static function findByUuid(string $uuid): ?static
  {
    return static::getContractRealizationClassName()::fromModel(
      static::getContractModelClassName()::where('uuid', $uuid)
        ->first()
    );
  }
}
