<?php

namespace Adscom\LarapackPaymentManager\Traits;

trait HasPaymentDriverMappings
{
  public static function getOrderContractClass(): string
  {
    return self::classname(self::CONTRACT_ORDER);
  }

  public static function getOrderItemContractClass(): string
  {
    return self::classname(self::CONTRACT_ORDER_ITEM);
  }

  public static function getOrderAddressContractClass(): string
  {
    return self::classname(self::CONTRACT_ORDER_ADDRESS);
  }

  public static function getPaymentContractClass(): string
  {
    return self::classname(self::CONTRACT_PAYMENT);
  }

  public static function getPaymentAccountContractClass(): string
  {
    return self::classname(self::CONTRACT_PAYMENT_ACCOUNT);
  }

  public static function getProcessorContractClass(): string
  {
    return self::classname(self::CONTRACT_PROCESSOR);
  }

  public static function getCompanyContractClass(): string
  {
    return self::classname(self::CONTRACT_COMPANY);
  }

  public static function getPaymentTokenContractClass(): string
  {
    return self::classname(self::CONTRACT_PAYMENT_TOKEN);
  }

  public static function setContract(string $contractClass): void
  {
    self::setModel($contractClass::getContractClassName(), $contractClass::getContractRealizationClassName());
  }
}
