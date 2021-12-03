<?php

namespace Adscom\LarapackPaymentManager\Interfaces;

interface IFinalizeHandler
{
  public function process(array $data): array;
}
