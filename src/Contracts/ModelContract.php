<?php

namespace Adscom\LarapackPaymentManager\Contracts;

use Illuminate\Database\Eloquent\Model;

abstract class ModelContract
{
  public function __construct(protected Model $model)
  {

  }

  public function getId(): int
  {
    return $this->model->id;
  }

  public static function fromModel(Model $model = null): ?static
  {
    if (!$model) {
      return null;
    }

    $className = static::class;
    return new $className($model);
  }

  abstract public static function getContractClassName(): string;

  abstract public static function getContractModelClassName(): string;

  abstract public static function getContractRealizationClassName(): string;
}
