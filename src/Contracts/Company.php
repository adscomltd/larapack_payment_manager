<?php

namespace Adscom\LarapackPaymentManager\Contracts;


abstract class Company extends ModelContract
{
  public static function getContractClassName(): string
  {
    return self::class;
  }
}
