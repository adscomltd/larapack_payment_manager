<?php

namespace Adscom\LarapackPaymentManager;

use App\Models\PaymentAccount;
use Adscom\LarapackPaymentManager\Drivers\PaymentDriver;
use Illuminate\Support\Manager;

class PaymentManager extends Manager
{
  protected string $defaultDriver = '';

  public function setDefaultDriver(string $uuid): PaymentDriver
  {
    /** @var PaymentAccount $paymentAccount */
    $paymentAccount = PaymentAccount::where('uuid', $uuid)->first();
    $driverName = $paymentAccount->processor->driver;

    /** @var PaymentDriver $driver */
    $driver = $this->driver($driverName);
    $driver->setup($paymentAccount);

    $this->defaultDriver = $driverName;
    return $driver;
  }

  public function getDefaultDriver(): string
  {
    return $this->defaultDriver;
  }
}
