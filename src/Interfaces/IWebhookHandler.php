<?php

namespace Adscom\LarapackPaymentManager\Interfaces;

use Adscom\LarapackPaymentManager\Contracts\Payment;

interface IWebhookHandler
{
  public function process(array $data = []): void;

  public function getPaymentForWebhook(array $data = []): Payment;

  public function isWebhookValid(array $data): bool;
}
