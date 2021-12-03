<?php

namespace Adscom\LarapackPaymentManager\Interfaces;

use App\Models\Payment;

interface IWebhookHandler
{
  public function process(array $data = []): void;

  public function getPaymentForWebhook(array $data = []): Payment;

  public function isWebhookValid(array $data): bool;
}
