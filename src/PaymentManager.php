<?php

namespace Adscom\LarapackPaymentManager;

use Adscom\LarapackPaymentManager\Contracts\PaymentAccount;
use Adscom\LarapackPaymentManager\Drivers\PaymentDriver;
use Illuminate\Support\Manager;

class PaymentManager extends Manager
{
  protected string $defaultDriver = '';

  public function setDefaultDriver(string $uuid): PaymentDriver
  {
    /** @var PaymentAccount $paymentAccount */
    $paymentAccount = PaymentDriver::getPaymentAccountContractClass()::findByUuid($uuid);
    $driverName = $paymentAccount->getProcessor()->getDriver();

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
