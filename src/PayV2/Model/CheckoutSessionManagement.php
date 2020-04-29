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

namespace Amazon\PayV2\Model;

use Amazon\PayV2\Api\Data\CheckoutSessionInterface;
use Magento\Quote\Api\Data\CartInterface;

class CheckoutSessionManagement implements \Amazon\PayV2\Api\CheckoutSessionManagementInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Amazon\PayV2\Api\Data\CheckoutSessionInterfaceFactory
     */
    private $checkoutSessionFactory;

    /**
     * @var \Amazon\PayV2\Api\CheckoutSessionRepositoryInterface
     */
    private $checkoutSessionRepository;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var Adapter\AmazonPayV2Adapter
     */
    private $amazonAdapter;

    /**
     * CheckoutSessionManagement constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Amazon\PayV2\Api\Data\CheckoutSessionInterfaceFactory $checkoutSessionFactory
     * @param \Amazon\PayV2\Api\CheckoutSessionRepositoryInterface $checkoutSessionRepository
     * @param AmazonConfig $amazonConfig
     * @param Adapter\AmazonPayV2Adapter $amazonAdapter
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Amazon\PayV2\Api\Data\CheckoutSessionInterfaceFactory $checkoutSessionFactory,
        \Amazon\PayV2\Api\CheckoutSessionRepositoryInterface $checkoutSessionRepository,
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Amazon\PayV2\Model\Adapter\AmazonPayV2Adapter $amazonAdapter
    )
    {
        $this->storeManager = $storeManager;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartManagement = $cartManagement;
        $this->cartRepository = $cartRepository;
        $this->orderRepository = $orderRepository;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->checkoutSessionRepository = $checkoutSessionRepository;
        $this->amazonConfig = $amazonConfig;
        $this->amazonAdapter = $amazonAdapter;
    }

    /**
     * @param mixed $cartId
     * @return CartInterface
     */
    protected function getCart($cartId)
    {
        if ($cartId instanceof CartInterface) {
            $result = $cartId;
        } elseif (is_numeric($cartId)) {
            $result = $this->cartRepository->getActive($cartId);
        } else {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            $result = $this->cartRepository->getActive($quoteIdMask->getQuoteId());
        }
        return $result;
    }

    /**
     * @param mixed $cartId
     * @return CheckoutSessionInterface
     */
    protected function getCheckoutSessionForCart($cartId)
    {
        return $this->checkoutSessionRepository->getActiveForCart($this->getCart($cartId));
    }

    /**
     * @param mixed $cartId
     * @param CheckoutSessionInterface $checkoutSession
     * @return bool
     */
    protected function canComplete($cartId, $checkoutSession)
    {
        return $this->getCart($cartId)->getIsActive() && $checkoutSession->getUpdatedAt();
    }

    /**
     * @param int $orderId
     * @param string $checkoutSessionId
     * @return $this
     */
    protected function updateChargePermission($orderId, $checkoutSessionId)
    {
        $order = $this->orderRepository->get($orderId);
        $checkoutSession = $this->amazonAdapter->getCheckoutSession($order->getStoreId(), $checkoutSessionId);
        $this->amazonAdapter->updateChargePermission($order, $checkoutSession['chargePermissionId']);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function createCheckoutSession($cartId)
    {
        $result = null;
        $this->cancelCheckoutSession($cartId);
        if ($this->amazonConfig->isEnabled()) {
            $response = $this->amazonAdapter->createCheckoutSession($this->storeManager->getStore()->getId());
            if (isset($response['checkoutSessionId'])) {
                $checkoutSession = $this->checkoutSessionFactory->create([
                    'data' => [
                        CheckoutSessionInterface::KEY_QUOTE_ID => $this->getCart($cartId)->getId(),
                        CheckoutSessionInterface::KEY_SESSION_ID => $response['checkoutSessionId'],
                    ]
                ]);
                $this->checkoutSessionRepository->save($checkoutSession);
                $result = $checkoutSession->getSessionId();
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelCheckoutSession($cartId)
    {
        if ($this->amazonConfig->isEnabled()) {
            $checkoutSession = $this->getCheckoutSessionForCart($cartId);
            if ($checkoutSession) {
                $checkoutSession->cancel();
                $this->checkoutSessionRepository->save($checkoutSession);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCheckoutSession($cartId)
    {
        $result = null;
        if ($this->amazonConfig->isEnabled()) {
            $checkoutSession = $this->getCheckoutSessionForCart($cartId);
            if ($checkoutSession) {
                $result = $checkoutSession->getSessionId();
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function updateCheckoutSession($cartId)
    {
        $result = null;
        $checkoutSession = null;
        $cart = $this->getCart($cartId);
        if ($this->amazonConfig->isEnabled()) {
            $checkoutSession = $this->getCheckoutSessionForCart($cart);
        }
        if ($checkoutSession && $cart->getIsActive()) {
            $response = $this->amazonAdapter->updateCheckoutSession($cart, $checkoutSession->getSessionId());
            if (!empty($response['webCheckoutDetail']['amazonPayRedirectUrl'])) {
                $result = $response['webCheckoutDetail']['amazonPayRedirectUrl'];
                $checkoutSession->setUpdated();
                $this->checkoutSessionRepository->save($checkoutSession);
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function completeCheckoutSession($cartId)
    {
        $result = null;
        $checkoutSession = null;
        $cart = $this->getCart($cartId);
        if ($this->amazonConfig->isEnabled()) {
            $checkoutSession = $this->getCheckoutSessionForCart($cart);
        }
        if ($checkoutSession && $this->canComplete($cart, $checkoutSession)) {
            if (!$cart->getCustomer()->getId()) {
                $cart->setCheckoutMethod(\Magento\Quote\Api\CartManagementInterface::METHOD_GUEST);
            }
            $result = $this->cartManagement->placeOrder($cart->getId());
            $this->updateChargePermission($result, $checkoutSession->getSessionId());
            $checkoutSession->complete();
            $this->checkoutSessionRepository->save($checkoutSession);
        }
        return $result;
    }
}
