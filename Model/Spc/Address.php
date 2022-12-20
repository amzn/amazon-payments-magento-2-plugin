<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\AddressInterface as SpcAddressInterface;
use Amazon\Pay\Helper\Spc\Cart;
use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Webapi\Exception as WebapiException;
use Amazon\Pay\Helper\Spc\ShippingMethod;
use Magento\Directory\Model\Currency;

class Address implements SpcAddressInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var AddressInterface
     */
    protected $address;

    /**
     * @var CheckoutSessionManagement
     */
    protected $checkoutSessionManager;

    /**
     * @var Cart
     */
    protected $cartHelper;

    /**
     * @var ShippingMethod
     */
    protected $shippingMethodHelper;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param AddressInterface $address
     * @param CheckoutSessionManagement $checkoutSessionManagement
     * @param Cart $cartHelper
     * @param ShippingMethod $shippingMethodHelper
     * @param Currency $currency
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AddressInterface $address,
        CheckoutSessionManagement $checkoutSessionManagement,
        Cart $cartHelper,
        ShippingMethod $shippingMethodHelper,
        Currency $currency
    )
    {
        $this->cartRepository = $cartRepository;
        $this->address = $address;
        $this->checkoutSessionManager = $checkoutSessionManagement;
        $this->cartHelper = $cartHelper;
        $this->shippingMethodHelper = $shippingMethodHelper;
        $this->currency = $currency;
    }

    /**
     * @inheritdoc
     */
    public function saveAddress(int $cartId, $cartDetails = null)
    {
        // Get quote
        try {
            /** @var $quote \Magento\Quote\Model\Quote */
            $quote = $this->cartRepository->getActive($cartId);
        } catch (NoSuchEntityException $e) {
            $this->cartHelper->logError('SPC Address: InvalidCartId. CartId: '. $cartId .' - ', $cartDetails);

            throw new \Magento\Framework\Webapi\Exception(
                new Phrase('InvalidCartId'), "Cart Id ". $cartId ." not found or inactive", 404
            );
        }

        $checkoutSessionId = $cartDetails['checkout_session_id'] ?? null;

        // Get addresses for updating
        if ($cartDetails && $checkoutSessionId) {
            $amazonSession = $this->checkoutSessionManager->getAmazonSession($checkoutSessionId);

            $amazonSessionStatus = $amazonSession['status'] ?? '404';
            if (!preg_match('/^2\d\d$/', $amazonSessionStatus)) {
                $this->cartHelper->logError(
                    'SPC Address: '. $amazonSession['reasonCode'] .'. CartId: '. $cartId .' - ', $cartDetails
                );

                throw new WebapiException(
                    new Phrase($amazonSession['reasonCode'])
                );
            }

            if ($amazonSession['statusDetails']['state'] !== 'Open') {
                $this->cartHelper->logError(
                    'SPC Address: '. $amazonSession['statusDetails']['reasonCode'] .'. CartId: '. $cartId .' - ', $cartDetails
                );

                throw new WebapiException(
                    new Phrase($amazonSession['statusDetails']['reasonCode'])
                );
            }

            // Get and set shipping address
            $magentoAddress = $this->checkoutSessionManager->getShippingAddress($checkoutSessionId);
            if (isset($magentoAddress[0])) {
                $shippingAddress = $this->address->setData($magentoAddress[0]);
                $quote->setShippingAddress($shippingAddress);
            }
            else {
                $this->cartHelper->logError(
                    'SPC Address: InvalidRequest - No shipping address. CartId: '. $cartId .' - ', $cartDetails
                );

                throw new \Magento\Framework\Webapi\Exception(
                    new Phrase('InvalidRequest'), "The Shipping Address is missing", 400
                );
            }
            // Get and set billing address
            $magentoAddress = $this->checkoutSessionManager->getBillingAddress($checkoutSessionId);
            if (isset($magentoAddress[0])) {
                $billingAddress = $this->address->setData($magentoAddress[0]);
                $quote->setBillingAddress($billingAddress);
            }
            else {
                $this->cartHelper->logError(
                    'SPC Address: InvalidRequest - No billing address. CartId: '. $cartId .' - ', $cartDetails
                );

                throw new \Magento\Framework\Webapi\Exception(
                    new Phrase('InvalidRequest'), "The Billing Address is missing", 400
                );
            }

            // TODO: Improve on keeping the correct currency code for multi-currency stores
            // Magento changes it when the store's currency doesn't match the quote's currency on API calls
            $quoteCurrency = $this->currency->load($quote->getQuoteCurrencyCode());
            $quote->setForcedCurrency($quoteCurrency);

            $this->cartRepository->save($quote);

            // check if a shipping method is already set
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod() ?? false;

            // set shipping method on the quote
            $this->shippingMethodHelper->setShippingMethodOnQuote($quote, $shippingMethod);
        }

        // Save and create response
        return $this->cartHelper->createResponse($quote->getId(), $checkoutSessionId);
    }
}
