<?php

namespace Adscom\LarapackPaymentManager;

use App\Models\Payment;
use Illuminate\Support\Arr;
use Str;

class PaymentResponse
{
  protected array $data = [];
  protected array $fillable = [
    'uuid',
    'response',
    'reason',
    'paid_amount',
    'processor_currency',
    'processor_status',
    'processor_transaction_id',
    'status',
    'payment_token_id',
    'notes',
    'is_webhook_payment',
    'is_bg_payment',
  ];

  protected array $frontend = [
    'uuid',
    'processor_transaction_id',
    'status',
  ];

  public static function fromData(array $data): self
  {
    $instance = new static();
    $instance->merge($data);

    return $instance;
  }

  public static function fromPayment(Payment $payment): self
  {
    return static::fromData($payment->toArray());
  }

  public function clone(): self
  {
    return static::fromData($this->data);
  }

  public function merge(array $data): void
  {
    $this->data = Arr::only(array_merge($this->data, $data), $this->fillable);
  }

  public function toArray(): array
  {
    return $this->data;
  }

  public function getFrontendData(): array
  {
    return Arr::only($this->data, $this->frontend);
  }

  public function setUuid(string $uuid): void
  {
    $this->data['uuid'] = $uuid;
  }

  public function setResponse(array $response): void
  {
    $this->data['response'] = $response;
  }

  public function setReason(string $reason): void
  {
    $this->data['reason'] = $reason;
  }

  public function setPaidAmount(float $amount): void
  {
    $this->data['paid_amount'] = $amount;
  }

  public function setProcessorCurrency(string $currency): void
  {
    $this->data['processor_currency'] = Str::upper($currency);
  }

  public function setProcessorStatus(string $status): void
  {
    $this->data['processor_status'] = $status;
  }

  public function setProcessorTransactionId(string $transactionId = null): void
  {
    $this->data['processor_transaction_id'] = $transactionId;
  }

  public function setStatus(int $status): void
  {
    $this->data['status'] = $status;
  }

  public function setPaymentTokenId(string $tokenId = null): void
  {
    $this->data['payment_token_id'] = $tokenId;
  }

  public function setNotes(array $notes): void
  {
    $this->data['notes'] = $notes;
  }

  public function setIsWebHookPayment(bool $isWebhookPayment): void
  {
    $this->data['is_webhook_payment'] = $isWebhookPayment;
  }

  public function getResponse(): ?array
  {
    return $this->data['response'] ?? null;
  }

  public function getStatus()
  {
    return $this->data['processor_status'] ?? null;
  }

  public function getReason()
  {
    return $this->data['reason'] ?? null;
  }

  public function getNotes()
  {
    return $this->data['notes'] ?? null;
  }

  public function getPaymentAmount()
  {
    return $this->data['payment_amount'] ?? null;
  }

  public function getUuid(): ?string
  {
    return $this->data['uuid'] ?? null;
  }
}
