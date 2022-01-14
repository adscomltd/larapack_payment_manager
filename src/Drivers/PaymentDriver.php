<?php

namespace Adscom\LarapackPaymentManager\Drivers;

use Adscom\LarapackPaymentManager\Contracts\Company;
use Adscom\LarapackPaymentManager\Contracts\Order;
use Adscom\LarapackPaymentManager\Contracts\OrderAddress;
use Adscom\LarapackPaymentManager\Contracts\OrderItem;
use Adscom\LarapackPaymentManager\Contracts\Payment;
use Adscom\LarapackPaymentManager\Contracts\PaymentAccount;
use Adscom\LarapackPaymentManager\Contracts\PaymentCard;
use Adscom\LarapackPaymentManager\Contracts\PaymentToken;
use Adscom\LarapackPaymentManager\Contracts\Processor;
use Adscom\LarapackPaymentManager\Interfaces\IMakeProcessData;
use Adscom\LarapackPaymentManager\Traits\HasPaymentDriverMappings;
use Adscom\LarapackPaymentManager\Traits\SupportsModelMapping;
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
  use SupportsModelMapping, HasPaymentDriverMappings;

  public const CONTRACT_PAYMENT_ACCOUNT = PaymentAccount::class;
  public const CONTRACT_PAYMENT = Payment::class;
  public const CONTRACT_PROCESSOR = Processor::class;
  public const CONTRACT_COMPANY = Company::class;
  public const CONTRACT_ORDER = Order::class;
  public const CONTRACT_ORDER_ITEM = OrderItem::class;
  public const CONTRACT_ORDER_ADDRESS = OrderAddress::class;
  public const CONTRACT_PAYMENT_CARD = PaymentCard::class;
  public const CONTRACT_PAYMENT_TOKEN = PaymentToken::class;

  public PaymentAccount $paymentAccount;
  public Order $order;
  public Processor $processor;
  public PaymentResponse $paymentResponse;
  public Payment $payment;
  public ?User $user;
  public bool $supportsWebhook = true;
  public bool $supportsFinalize = true;

  protected Company $company;
  protected ?PaymentCard $paymentCard;
  protected array $paymentAccountData;
  public array $config;
  protected string $declineMessage;

  abstract public function handleResponse($response): PaymentResponse;

  public function __construct()
  {
    $this->paymentResponse = PaymentResponse::fromData(['uuid' => Str::uuid()->toString()]);
  }

  public function setup(PaymentAccount $paymentAccount): void
  {
    $this->paymentAccount = $paymentAccount;
    $this->processor = $this->paymentAccount->getProcessor();
    $this->company = $this->paymentAccount->getCompany();
    $this->user = auth()->user();
    $this->paymentAccountData = $this->getPaymentAccountData();
    $this->config = array_merge(
      $this->processor->getConfig(),
      $this->paymentAccount->getConfig(),
    );
  }

  public function getConfig(): array
  {
    return $this->config;
  }

  public function getProcessorCurrency(): string
  {
    return $this->config['currency'];
//    $current = config('currency.current')?->code;
//
//    if ($current && in_array($current, $this->getSupportedCurrencies(), true)) {
//      return $current;
//    }
//
//    return $this->getSupportedCurrencies()[0] ?? config('currency.default');
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
    $this->paymentResponse->setStatus(self::getPaymentContractClass()::getInitiatedStatus());
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
      $this->paymentResponse->setStatus(self::getPaymentContractClass()::getErrorStatus());
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

    $this->payment = self::getPaymentContractClass()::create(array_merge($paymentResponse->toArray(), [
      'order_id' => $this->order->getId(),
      'processor_id' => $this->processor->getId(),
      'payment_account_id' => $this->paymentAccount->getId(),
    ]));

    return $this->payment;
  }

  final public function finalize(array $data): array
  {
    $this->order = $this->payment->getOrder();

    $this->setup($this->payment->getAccount());

    return $this->finalizeHandler->process($data);
  }

  final public function isWebhookUnique(): bool
  {
    $hashKey = 'someKey';
    $hash = request()?->hash($hashKey);

    /** @var Payment $payment */
    foreach ($this->order->getPayments() as $payment) {
      $currentHash = Arr::hash($payment->getResponse(), $hashKey);

      if ($hash === $currentHash) {
        return false;
      }
    }

    return true;
  }

  final public function webhook(array $data): void
  {
    $this->payment = $this->webhookHandler->getPaymentForWebhook($data);
    $this->order = $this->payment->getOrder();

    $this->setup($this->payment->getAccount());

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
      'payment_uuid' => $this->paymentResponse->getUuid(),
      'payment_card_uuid' => $this->paymentCard?->getUuid(),
      'payment_response' => $this->paymentResponse->toArray(),
      'payment_account_data' => $this->paymentAccountData,
    ];

    return array_merge($data, $additional);
  }

  public function getFinalizeUrl(array $additional = [], array $options = []): string
  {
    return route('front.payment.finalize', array_merge([
      'processor' => $this->processor->getDriver(),
      'uuid' => $this->paymentResponse->getUuid(),
    ], $additional));
  }

  abstract protected function getPaymentStatus(string $status): int;

  protected function prepareData(array $data): array
  {
    return $data;
  }

  public function setUser(User $user): void
  {
    $this->user = $user;
    $this->paymentAccountData = $this->getPaymentAccountData();
  }

  protected function setOrder(Order $order): void
  {
    $this->order = $order;
  }

  protected function getPaymentAccountData(): array
  {
    return $this->paymentAccount->getData($this->user);
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
      && !($this instanceof ITokenable)
      && $this->paymentCard
      && $this->processor->isCC()
      && str_starts_with($this->paymentCard->getCVC() ?? '', '0')
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
