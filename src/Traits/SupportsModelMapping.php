<?php

namespace Adscom\LarapackPaymentManager\Traits;

trait SupportsModelMapping
{
  /**
   * Set the model to be used for roles.
   *
   * @param  string  $model
   * @param  string  $mappedClass
   * @return void
   */
  public static function setModel(string $model, string $mappedClass): void
  {
    static::$models[$model] = $mappedClass;
  }

  /**
   * Map of bouncer's models.
   *
   * @var array
   */
  protected static array $models = [];

  /**
   * Get the classname mapping for the given model.
   *
   * @param  string  $model
   * @return string
   */
  public static function classname(string $model): string
  {
    return static::$models[$model] ?? $model;
  }
}
