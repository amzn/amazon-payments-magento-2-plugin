<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\AddressInterface as SpcAddressInterface;
use Amazon\Pay\Api\Spc\ShippingMethodInterface;
use Amazon\Pay\Helper\Spc\Cart;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Directory\Model\Region;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Webapi\Exception as WebapiException;

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
     * @var Region
     */
    protected $region;

    /**
     * @var ShippingInformationManagementInterface
     */
    protected $shippingInformationManagement;

    /**
     * @var ShippingInformationInterface
     */
    protected $shippingInformation;

    /**
     * @var CheckoutSessionManagement
     */
    protected $checkoutSessionManager;

    /**
     * @var ShippingMethodInterface
     */
    protected $shippingMethod;

    /**
     * @var Cart
     */
    protected $cartHelper;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param AddressInterface $address
     * @param Region $region
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param ShippingInformationInterface $shippingInformation
     * @param CheckoutSessionManagement $checkoutSessionManagement
     * @param ShippingMethodInterface $shippingMethod
     * @param Cart $cartHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AddressInterface $address,
        Region $region,
        ShippingInformationManagementInterface $shippingInformationManagement,
        ShippingInformationInterface $shippingInformation,
        CheckoutSessionManagement $checkoutSessionManagement,
        ShippingMethodInterface $shippingMethod,
        Cart $cartHelper
    )
    {
        $this->cartRepository = $cartRepository;
        $this->address = $address;
        $this->region = $region;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformation = $shippingInformation;
        $this->checkoutSessionManager = $checkoutSessionManagement;
        $this->shippingMethod = $shippingMethod;
        $this->cartHelper = $cartHelper;
    }

    /**
     * @inheritdoc
     */
    public function saveAddress(int $cartId, $cartDetails = null)
    {
        // Get quote
        $quote = $this->cartRepository->get($cartId);

        $checkoutSessionId = $cartDetails['checkoutSessionId'] ?? null;

        // Get addresses for updating
        if ($cartDetails && $checkoutSessionId) {
            $amazonSession = $this->checkoutSessionManager->getAmazonSession($checkoutSessionId);

            $amazonSessionStatus = $amazonSession['status'] ?? '404';
            if (!preg_match('/^2\d\d$/', $amazonSessionStatus)) {
                throw new WebapiException(
                    new Phrase($amazonSession['reasonCode'])
                );
            }

            if ($amazonSession['statusDetails']['state'] !== 'Open') {
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
                throw new WebapiException(
                    new Phrase('InvalidRequest')
                );
            }
            // Get and set billing address
            $magentoAddress = $this->checkoutSessionManager->getBillingAddress($checkoutSessionId);
            if (isset($magentoAddress[0])) {
                $billingAddress = $this->address->setData($magentoAddress[0]);
                $quote->setBillingAddress($billingAddress);
            }
            else {
                throw new WebapiException(
                    new Phrase('InvalidRequest')
                );
            }

            $this->shippingMethod->shippingMethod($cartId, $cartDetails);
        }

        // Save and create response
        return $this->cartHelper->saveAndCreateResponse($quote, $checkoutSessionId);
    }
}
