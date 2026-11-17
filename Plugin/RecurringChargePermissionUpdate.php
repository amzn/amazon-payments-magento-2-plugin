<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Amazon\Pay\Plugin;

use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Amazon\Pay\Model\Subscription\SubscriptionManager;
use Amazon\Pay\Helper\SubscriptionHelper;

class RecurringChargePermissionUpdate
{
    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    private $quoteResource;

    /**
     * @var AmazonPayAdapter
     */
    private $amazonAdapter;

    /**
     * @var SubscriptionManager
     */
    private $subscriptionManager;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Plugin constructor
     *
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param CartRepositoryInterface $cartRepository
     * @param AmazonPayAdapter $amazonAdapter
     * @param SubscriptionManager $subscriptionManager
     * @param SubscriptionHelper $subscriptionHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Quote\Model\ResourceModel\Quote $quoteResource
     */
    public function __construct(
        PaymentTokenManagementInterface $paymentTokenManagement,
        CartRepositoryInterface $cartRepository,
        AmazonPayAdapter $amazonAdapter,
        SubscriptionManager $subscriptionManager,
        SubscriptionHelper $subscriptionHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Quote\Model\ResourceModel\Quote $quoteResource
    ) {
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->cartRepository = $cartRepository;
        $this->quoteResource = $quoteResource;
        $this->amazonAdapter = $amazonAdapter;
        $this->subscriptionManager = $subscriptionManager;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->logger = $logger;
    }

    /**
     * Check to see if a stored AP token needs recurring metadata updated before using it for another recurring charge.
     *
     * @param PaymentInformationManagement $subject
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return array
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        ?\Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($paymentMethod->getMethod() === 'amazon_payment_v2_vault') {
            $quote = $this->cartRepository->getActive($cartId);

            if ($this->subscriptionManager->hasSubscription($quote)) {
                $customerId = $quote->getBillingAddress()
                    ->getCustomerId();

                $chargePermissionId = $this->paymentTokenManagement
                    ->getByPublicHash($paymentMethod->getAdditionalData()['public_hash'], $customerId)
                    ->getGatewayToken();

                $chargePermission = $this->amazonAdapter->getChargePermission(
                    $quote->getStoreId(),
                    $chargePermissionId
                );
                $newFrequency = $this->amazonAdapter->getRecurringMetadata($quote)['frequency'];
                $oldFrequency = $chargePermission['recurringMetadata']['frequency'];
                if ($this->subscriptionHelper->hasShorterFrequency($newFrequency, $oldFrequency)) {
                    if (!$quote->getReservedOrderId()) {
                        try {
                            $quote->reserveOrderId();
                            $this->quoteResource->save($quote);
                        } catch (\Exception $e) {
                            $this->logger->debug($e->getMessage());
                        }
                    }

                    $payload = [
                        'merchantReferenceId' => $quote->getReservedOrderId(),
                        'recurringMetadata' => [
                            'frequency' => $newFrequency
                        ]
                    ];

                    $this->amazonAdapter->updateChargePermission(
                        $quote->getStoreId(),
                        $chargePermissionId,
                        $payload
                    );
                }
            }
        }

        return [$cartId, $paymentMethod, $billingAddress];
    }
}
