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
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Webapi\Exception as WebapiException;

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
     * @var \Magento\Framework\Validator\Factory
     */
    private $validatorFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    private $countryCollectionFactory;

    /**
     * @var \Amazon\PayV2\Domain\AmazonAddressFactory
     */
    private $amazonAddressFactory;

    /**
     * @var \Amazon\PayV2\Helper\Address
     */
    private $addressHelper;

    /**
     * @var \Amazon\PayV2\Api\Data\CheckoutSessionInterfaceFactory
     */
    private $checkoutSessionFactory;

    /**
     * @var \Amazon\PayV2\Api\CheckoutSessionRepositoryInterface
     */
    private $checkoutSessionRepository;

    /**
     * @var \Amazon\PayV2\Helper\Data
     */
    private $amazonHelper;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var Adapter\AmazonPayV2Adapter
     */
    private $amazonAdapter;

    /**
     * @var array
     */
    private $amazonSessions = [];

    /**
     * @var array
     */
    private $carts = [];

    /**
     * CheckoutSessionManagement constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Framework\Validator\Factory $validatorFactory,
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
     * @param \Amazon\PayV2\Domain\AmazonAddressFactory $amazonAddressFactory,
     * @param \Amazon\PayV2\Helper\Address $addressHelper,
     * @param \Amazon\PayV2\Api\Data\CheckoutSessionInterfaceFactory $checkoutSessionFactory
     * @param \Amazon\PayV2\Api\CheckoutSessionRepositoryInterface $checkoutSessionRepository
     * @param \Amazon\PayV2\Helper\Data $amazonHelper
     * @param AmazonConfig $amazonConfig
     * @param Adapter\AmazonPayV2Adapter $amazonAdapter
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Amazon\PayV2\Domain\AmazonAddressFactory $amazonAddressFactory,
        \Amazon\PayV2\Helper\Address $addressHelper,
        \Amazon\PayV2\Api\Data\CheckoutSessionInterfaceFactory $checkoutSessionFactory,
        \Amazon\PayV2\Api\CheckoutSessionRepositoryInterface $checkoutSessionRepository,
        \Amazon\PayV2\Helper\Data $amazonHelper,
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Amazon\PayV2\Model\Adapter\AmazonPayV2Adapter $amazonAdapter
    )
    {
        $this->storeManager = $storeManager;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartManagement = $cartManagement;
        $this->cartRepository = $cartRepository;
        $this->validatorFactory = $validatorFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->amazonAddressFactory = $amazonAddressFactory;
        $this->addressHelper = $addressHelper;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->checkoutSessionRepository = $checkoutSessionRepository;
        $this->amazonHelper = $amazonHelper;
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
        } else {
            if (!isset($this->carts[$cartId])) {
                if (is_numeric($cartId)) {
                    $cart = $this->cartRepository->getActive($cartId);
                } else {
                    $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
                    $cart = $this->cartRepository->getActive($quoteIdMask->getQuoteId());
                }
                $this->carts[$cartId] = $cart;
            }
            $result = $this->carts[$cartId];
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
     * @param mixed $cartId
     * @return mixed
     */
    protected function getAmazonSession($cartId)
    {
        $amazonSessionId = $this->getCheckoutSession($cartId);
        if (!isset($this->amazonSessions[$amazonSessionId])) {
            $this->amazonSessions[$amazonSessionId] = $this->amazonAdapter->getCheckoutSession($this->storeManager->getStore()->getId(), $amazonSessionId);
        }
        return $this->amazonSessions[$amazonSessionId];
    }

    /**
     * @param mixed $cartId
     * @param bool $isShippingAddress
     * @param mixed $addressDataExtractor
     * @return mixed
     */
    protected function fetchAddress($cartId, $isShippingAddress, $addressDataExtractor)
    {
        $result = false;
        if ($this->amazonConfig->isEnabled()) {
            $session = $this->getAmazonSession($cartId);

            $addressData = call_user_func($addressDataExtractor, $session);
            if (!empty($addressData)) {
                $addressData['state'] = $addressData['stateOrRegion'];
                $addressData['phone'] = $addressData['phoneNumber'];

                $address = array_combine(
                    array_map('ucfirst', array_keys($addressData)),
                    array_values($addressData)
                );

                $result = $this->convertToMagentoAddress($address, $isShippingAddress);
                $result[0]['email'] = $session['buyer']['email'];
            }
        }
        return $result;
    }

    /**
     * @param array $address
     * @param boolean $isShippingAddress
     * @return array
     */
    protected function convertToMagentoAddress(array $address, $isShippingAddress = false)
    {
        $amazonAddress  = $this->amazonAddressFactory->create(['address' => $address]);
        $magentoAddress = $this->addressHelper->convertToMagentoEntity($amazonAddress);

        if ($isShippingAddress) {
            $validator = $this->validatorFactory->createValidator('amazon_address', 'on_select');

            if (!$validator->isValid($magentoAddress)) {
                throw new ValidatorException(null, null, [$validator->getMessages()]);
            }

            $countryCollection = $this->countryCollectionFactory->create();

            $collectionSize = $countryCollection->loadByStore()
                ->addFieldToFilter('country_id', ['eq' => $magentoAddress->getCountryId()])
                ->setPageSize(1)
                ->setCurPage(1)
                ->getSize();

            if (1 != $collectionSize) {
                throw new WebapiException(__('the country for your address is not allowed for this store'));
            }
        }

        return [$this->addressHelper->convertToArray($magentoAddress)];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($cartId)
    {
        $result = [];
        if ($this->amazonConfig->isEnabled()) {
            $result = [
                'merchant_id' => $this->amazonConfig->getMerchantId(),
                'currency' => $this->amazonConfig->getCurrencyCode(),
                'button_color' => $this->amazonConfig->getButtonColor(),
                'language' => $this->amazonConfig->getLanguage(),
                'pay_only' => $this->amazonHelper->isPayOnly($this->getCart($cartId)),
                'sandbox' => $this->amazonConfig->isSandboxEnabled(),
            ];
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function createCheckoutSession($cartId)
    {
        $result = [];
        $this->cancelCheckoutSession($cartId);
        if ($this->amazonConfig->isEnabled()) {
            $result = $this->amazonAdapter->createCheckoutSession($this->storeManager->getStore()->getId());
            if (isset($result['checkoutSessionId'])) {
                $checkoutSession = $this->checkoutSessionFactory->create([
                    'data' => [
                        CheckoutSessionInterface::KEY_QUOTE_ID => $this->getCart($cartId)->getId(),
                        CheckoutSessionInterface::KEY_SESSION_ID => $result['checkoutSessionId'],
                    ]
                ]);
                $this->checkoutSessionRepository->save($checkoutSession);
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAddress($cartId)
    {
        return $this->fetchAddress($cartId, true, function ($session) {
            return $session['shippingAddress'] ?? [];
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingAddress($cartId)
    {
        return $this->fetchAddress($cartId, false, function ($session) {
            return $session['paymentPreferences'][0]['billingAddress'] ?? [];
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentDescriptor($cartId)
    {
        $session = $this->getAmazonSession($cartId);
        return $session['paymentPreferences'][0]['paymentDescriptor'] ?? '';
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
            try {
                if (!$cart->getCustomer()->getId()) {
                    $cart->setCheckoutMethod(\Magento\Quote\Api\CartManagementInterface::METHOD_GUEST);
                }
                $result = $this->cartManagement->placeOrder($cart->getId());
                $checkoutSession->complete();
                $this->checkoutSessionRepository->save($checkoutSession);
            } catch (\Exception $e) {
                $session = $this->amazonAdapter->getCheckoutSession($cart->getStoreId(), $checkoutSession->getSessionId());
                if (isset($session['chargePermissionId'])) {
                    $response = $this->amazonAdapter->closeChargePermission($cart->getStoreId(), $session['chargePermissionId'], 'Canceled due to technical issue: ' . $e->getMessage(), true);
                }
                $this->cancelCheckoutSession($cartId);
                throw $e;
            }
        }
        return $result;
    }
}
