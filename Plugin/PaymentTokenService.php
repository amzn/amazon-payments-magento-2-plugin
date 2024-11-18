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

use ParadoxLabs\Subscriptions\Model\Service\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Amazon\Pay\Gateway\Config\Config;
use Amazon\Pay\Helper\SubscriptionHelper;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;

class PaymentTokenService
{
    /**
     * @var SubscriptionHelper
     */
    private $helper;

    /**
     * @var AmazonPayAdapter
     */
    private $amazonAdapter;

    /**
     * @param SubscriptionHelper $helper
     * @param AmazonPayAdapter $amazonAdapter
     */
    public function __construct(
        SubscriptionHelper $helper,
        AmazonPayAdapter $amazonAdapter
    ) {
        $this->helper = $helper;
        $this->amazonAdapter = $amazonAdapter;
    }

    /**
     * Filter card tokens
     *
     * @param Payment $paymentService
     * @param PaymentTokenInterface[] $cards
     * @param CartInterface $quote
     * @return PaymentTokenInterface[]
     */
    public function afterGetActiveCustomerCardsForQuote(
        Payment $paymentService,
        $cards,
        CartInterface $quote
    ) {
        if ($quote->getPayment()->getMethod() === Config::VAULT_CODE) {
            // Only show the token associated with this subscription if the customer has multiple
            // AP tokens using the same card
            $filteredCards = array_filter($cards, function ($card) use ($paymentService, $quote) {
                return $card->getId() === $paymentService->getQuoteCard($quote)->getId();
            });
            $paymentDescriptors[] = $this->helper->getTokenPaymentDescriptor(reset($filteredCards));

            // If the customer has multiple AP tokens using a different card, only display one
            // entry per card
            foreach ($cards as $card) {
                if ($card->getPaymentMethodCode() === Config::CODE) {
                    $currentPaymentDescriptor = $this->helper->getTokenPaymentDescriptor($card);

                    if (!in_array($currentPaymentDescriptor, $paymentDescriptors)) {
                        $filteredCards[] = $card;
                        $paymentDescriptors[] = $currentPaymentDescriptor;
                    }
                } else {
                    $filteredCards[] = $card;
                }
            }

            return $filteredCards;
        }

        return array_filter($cards, function ($card) {
            return $card->getPaymentMethodCode() !== Config::CODE;
        });
    }

    /**
     * Update charge permission where needed
     *
     * @param Payment $paymentService
     * @param Quote $quote
     * @param PaymentTokenInterface $card
     * @param array $paymentData
     * @return mixed
     */
    public function beforeUpdatePayment(
        Payment $paymentService,
        Quote $quote,
        PaymentTokenInterface $card,
        array $paymentData
    ) {
        $previousToken = $paymentService->getQuoteCard($quote);
        if ($previousToken->getPaymentMethodCode() === Config::CODE) {
            // Check previous token later in afterUpdatePayment
            $paymentData[] = $previousToken;

            // If moving to another AP method, update its charge permission if the subscription's
            // frequency is shorter than the recurring metadata associated with it
            if ($card->getPaymentMethodCode() === Config::CODE) {
                $toChargePermission = $this->amazonAdapter->getChargePermission(
                    $quote->getStoreId(),
                    $card->getGatewayToken()
                );
                $oldFrequency = $toChargePermission['recurringMetadata']['frequency'];
                $newFrequency = $this->amazonAdapter->getRecurringMetadata($quote)['frequency'];

                if ($this->helper->hasShorterFrequency($newFrequency, $oldFrequency)) {
                    $merchantReferenceId = $this->amazonAdapter->getChargePermission(
                        $quote->getStoreId(),
                        $paymentService->getQuoteCard($quote)->getGatewayToken()
                    )['merchantMetadata']['merchantReferenceId'];

                    $payload = [
                        'merchantReferenceId' => $merchantReferenceId,
                        'recurringMetadata' => [
                            'frequency' => $newFrequency
                        ]
                    ];

                    $this->amazonAdapter->updateChargePermission(
                        $quote->getStoreId(),
                        $card->getGatewayToken(),
                        $payload
                    );
                }
            }
        }

        return [$quote, $card, $paymentData];
    }

    /**
     * Delete the previous token if this was the last subscription it was used for
     *
     * @param Payment $paymentService
     * @param mixed $result
     * @param Quote $quote
     * @param PaymentTokenInterface $card
     * @param array $paymentData
     * @return void
     */
    public function afterUpdatePayment(
        Payment $paymentService,
        $result,
        Quote $quote,
        PaymentTokenInterface $card,
        array $paymentData
    ) {
        if (!empty($paymentData)) {
            foreach ($paymentData as $data) {
                if ($data instanceof \Magento\Vault\Model\PaymentToken
                    && $data->getPaymentMethodCode() === Config::CODE) {
                    $subscriptionsPaidWithToken = $this->helper->getSubscriptionsPaidWithToken($data);
                    if (empty($subscriptionsPaidWithToken)) {
                        $this->helper->cancelToken($quote, $data);
                    }
                }
            }
        }

        return $result;
    }
}
