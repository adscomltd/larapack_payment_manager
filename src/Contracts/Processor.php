<?php

namespace Adscom\LarapackPaymentManager\Contracts;


abstract class Processor extends ModelContract
{
  public static function getContractClassName(): string
  {
    return self::class;
  }

  public function getUuid(): string
  {
    return (string) $this->model->uuid;
  }

  public function getDriver(): string
  {
    return $this->model->driver;
  }

  public function getConfig(): array
  {
    return $this->model->config ?? [];
  }

  abstract public function isCC(): bool;
}
