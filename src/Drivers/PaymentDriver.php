<?php

namespace Adscom\LarapackPaymentManager\Drivers;

use Adscom\LarapackPaymentManager\Interfaces\IMakeProcessData;
use App\Models\Company;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\PaymentCard;
use App\Models\PaymentGateway;
use App\Models\Processor;
use App\Models\User;
use Arr;
use Adscom\LarapackPaymentManager\Interfaces\ITokenable;
use Adscom\LarapackPaymentManager\Exceptions\PaymentRedirectionException;
use Adscom\LarapackPaymentManager\Interfaces\IPaymentDriver;
use Adscom\LarapackPaymentManager\PaymentResponse;
use Exception;
use Illuminate\Support\Str;

abstract class PaymentDriver implements IPaymentDriver
{
  public PaymentAccount $paymentAccount;
  public Order $order;
  public Processor $processor;
  public PaymentResponse $paymentResponse;
  public Payment $payment;
  public bool $supportsWebhook = true;
  public bool $supportsFinalize = true;

  protected Company $company;
  protected ?PaymentCard $paymentCard;
  protected ?User $user;
  protected array $paymentAccountData;
  public array $config;
  protected string $declineMessage;

  abstract public function handleResponse($response): PaymentResponse;

  public function __construct()
  {
    $this->payment = new Payment();
    $this->payment->uuid = Str::uuid()->toString();

    $this->paymentResponse = PaymentResponse::fromPayment($this->payment);
  }

  public function setup(PaymentAccount $paymentAccount): void
  {
    $this->paymentAccount = $paymentAccount;
    $this->processor = $this->paymentAccount->processor;
    $this->company = $this->paymentAccount->company;
    $this->user = auth()->user();
    $this->paymentAccountData = $this->getPaymentAccountData();
    $this->config = array_merge(
      $this->processor->config ?? [],
      $this->paymentAccount->config ?? [],
    );
  }

  public function getConfig(): array
  {
    return $this->config;
  }

  public function getProcessorCurrency(): string
  {
    $current = config('currency.current')?->code;

    if ($current && in_array($current, $this->getSupportedCurrencies(), true)) {
      return $current;
    }

    return $this->getSupportedCurrencies()[0] ?? config('currency.default');
  }

  /**
   * @param  Order  $order
   * @param  IMakeProcessData  $dto
   * @return array
   * @throws PaymentRedirectionException
   */
  final public function process(Order $order, IMakeProcessData $dto): array
  {
    $this->paymentCard = $dto->getPaymentCard();
    $this->order = $order;

    $data = $this->purify($dto->getPostData());

    if ($this->shouldBeDeclined($data)) {
      dd('Decline imitation');
      // todo: return result
    }

    $exception = null;

    try {
      $this->processPayment($data);
    } catch (PaymentRedirectionException $e) {
      $this->handlePaymentRedirectException($e);

      $exception = $e;
    } catch (Exception $e) {
      $this->handleException($e);

      $exception = $e;
    }

    $this->createPaymentFromResponse();

    if ($exception) {
      throw $exception; // for report in controller
    }

    return $this->paymentResponse->getFrontendData();
  }

  protected function handlePaymentRedirectException(PaymentRedirectionException $e): void
  {
    $this->paymentResponse->setStatus(Payment::STATUS_INITIATED);
    $this->paymentResponse->setResponse($e->response);
    $this->paymentResponse->setReason($e->reason);
    $this->paymentResponse->setNotes($e->notes);
  }

  protected function handleException(Exception $e): void
  {
    if (!$this->paymentResponse->getReason()) {
      $this->paymentResponse->setReason($e->getMessage());
    }

    if (!$this->paymentResponse->getResponse()) {
      $this->paymentResponse->setResponse(['error' => $e->getMessage()]);
    }

    if (!$this->paymentResponse->getStatus()) {
      $this->paymentResponse->setStatus(Payment::STATUS_ERROR);
    }
  }

  /**
   * Creates a new payment from driver's own or provided payment response
   * @param  PaymentResponse|null  $response
   * @return Payment
   */
  public function createPaymentFromResponse(PaymentResponse $response = null): Payment
  {
    $paymentResponse = $response ?? $this->paymentResponse;

    $this->payment = Payment::create(array_merge($paymentResponse->toArray(), [
      'order_id' => $this->order->id,
      'processor_id' => $this->processor->id,
      'payment_account_id' => $this->paymentAccount->id,
    ]));

    $this->payment->save();

    return $this->payment;
  }

  final public function finalize(array $data): array
  {
    $this->order = $this->payment->order;

    $this->setup($this->payment->account);

    return $this->finalizeHandler->process($data);
  }

  final public function isWebhookUnique(): bool
  {
    $hashKey = 'someKey';
    $hash = request()?->hash($hashKey);

    foreach ($this->order->payments as $payment) {
      $currentHash = Arr::hash($payment->response, $hashKey);

      if ($hash === $currentHash) {
        return false;
      }
    }

    return true;
  }

  final public function webhook(array $data): void
  {
    $this->payment = $this->webhookHandler->getPaymentForWebhook($data);
    $this->order = $this->payment->order;

    $this->setup($this->payment->account);

    if (!$this->isWebhookUnique()) {
      abort(400, 'Duplicate webhook payload');
    }

    if (!$this->webhookHandler->isWebhookValid($data)) {
      abort(400, 'Invalid webhook payload');
    }

    $this->webhookHandler->process($data);
  }

  public function getCurrentMetaData(array $additional = [], array $options = []): array
  {
    $data = [
      'payment_uuid' => $this->payment->uuid,
      'payment_card_uuid' => $this->paymentCard?->uuid,
      'payment_response' => $this->paymentResponse->toArray(),
      'payment_account_data' => $this->paymentAccountData,
    ];

    return array_merge($data, $additional);
  }

  public function getFinalizeUrl(array $additional = [], array $options = []): string
  {
    return route('front.payment.finalize', array_merge([
      'processor' => $this->processor->driver,
      'uuid' => $this->payment->uuid,
    ], $additional));
  }

  abstract protected function getPaymentStatus(string $status): int;

  protected function prepareData(array $data): array
  {
    return $data;
  }

  protected function setUser(User $user): void
  {
    $this->user = $user;
  }

  protected function setOrder(Order $order): void
  {
    $this->order = $order;
  }

  protected function getPaymentAccountData(): array
  {
    $paymentAccountData = $this->paymentAccount->userData?->data;

    return $paymentAccountData ?? [];
  }

  protected function getSupportedCurrencies(): array
  {
    return $this->config['supported_currencies'] ?? [];
  }

  protected function purify(array $data): array
  {
    return $data;
  }

  protected function shouldBeDeclined(array $data): bool
  {
    // set declined for CC cvc starting from 0
    if (!app()->environment('production')
      && $this->processor->paymentGateway->name === PaymentGateway::GATEWAY_CC
      && !($this->processor instanceof ITokenable)
      && $this->paymentCard
      && substr($this->paymentCard->cvc ?? '', 0, 1) === '0'
    ) {
      $this->setDeclineMessage('Decline Imitation');
      return true;
    }

    return false;
  }

  protected function getDeclineMessage(): string
  {
    return $this->declineMessage;
  }

  protected function setDeclineMessage(string $message): void
  {
    $this->declineMessage = $message;
  }
}
