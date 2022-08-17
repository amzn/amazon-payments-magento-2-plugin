<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amazon\Pay\Gateway\Response;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use Amazon\Pay\Model\AmazonConfig;
use Amazon\Pay\Gateway\Helper\SubjectReader;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Model\PaymentTokenFactory;
use Amazon\Pay\Model\Subscription\SubscriptionManager;
use Magento\Quote\Api\CartRepositoryInterface;
use RuntimeException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultDetailsHandler implements HandlerInterface
{
    /**
     * @var PaymentTokenInterfaceFactory
     */
    protected $paymentTokenFactory;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    protected $paymentExtensionFactory;

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var SubscriptionManager
     */
    private $subscriptionManager;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;
    

    /**
     * VaultDetailsHandler constructor.
     *
     * @param PaymentTokenFactory $paymentTokenFactory
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param SubscriptionManager $subscriptionManager
     * @param CartRepositoryInterface $quoteRepository
     * @param AmazonConfig $config
     * @param SubjectReader $subjectReader
     * @throws RuntimeException
     */
    public function __construct(
        PaymentTokenFactory $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        SubscriptionManager $subscriptionManager,
        CartRepositoryInterface $quoteRepository,
        AmazonConfig $config,
        SubjectReader $subjectReader
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->subscriptionManager = $subscriptionManager;
        $this->quoteRepository = $quoteRepository;
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!$this->config->isVaultEnabled()) {
            return null;
        }

        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        $quoteId = $payment->getOrder()->getQuoteId();
        $quote = $this->quoteRepository->get($quoteId);

        if (!$this->subscriptionManager->hasSubscription($quote)) {
            return null;
        }

        // add vault payment token entity to extension attributes
        $paymentToken = $this->getVaultPaymentToken($response);
        if (null !== $paymentToken) {
            $extensionAttributes = $this->getExtensionAttributes($payment);
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }
    }

    /**
     * Get vault payment token entity
     *
     * @param $response
     * @return PaymentTokenInterface|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function getVaultPaymentToken($response)
    {
      	if (!isset($response['checkoutSessionId'])) {
      		return null;
      	}
        
        $token = $response['checkoutSessionId'];

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create(PaymentTokenFactory::TOKEN_TYPE_ACCOUNT);
        $paymentToken->setGatewayToken($token);
        $paymentToken->setExpiresAt($this->getExpirationDate());
        $details = json_encode([
            'checkoutSessionId' => $response['checkoutSessionId'],
            'paymentPreferences' => $response['paymentPreferences']
        ]);
        $paymentToken->setTokenDetails($details);
        return $paymentToken;
    }
   
    /**
     * Get payment extension attributes
     *
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment): OrderPaymentExtensionInterface
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    private function getExpirationDate(): string
    {
        $expDate = new DateTime('NOW',new DateTimeZone('UTC'));
        $expDate->add(new DateInterval('P1Y'));
        return $expDate->format('Y-m-d 00:00:00');
    }
}
