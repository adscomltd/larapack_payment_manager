<?php

namespace Adscom\LarapackPaymentManager\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

abstract class PaymentToken extends ModelContract
{
  public static function getContractClassName(): string
  {
    return self::class;
  }

  public static function create(array $data = []): self
  {
    return static::getContractRealizationClassName()::fromModel(static::getContractModelClassName()::create($data));

  }

  public static function find(array $filters): ?static
  {
    /** @var Builder $builder */
    $builder = static::getContractModelClassName()::query();

    foreach ($filters as $field => $value) {
      $builder->where($field, $value);
    }

    return static::getContractRealizationClassName()::fromModel($builder->first());
  }
}
