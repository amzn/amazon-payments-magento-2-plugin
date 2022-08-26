<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\AddressInterface as SpcAddressInterface;
use Amazon\Pay\Helper\Spc\Cart;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Directory\Model\Region;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
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
     * @var AmazonPayAdapter
     */
    protected $amazonPayAdapter;

    /**
     * @var CheckoutSessionManagement
     */
    protected $checkoutSessionManager;

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
     * @param AmazonPayAdapter $amazonPayAdapter
     * @param CheckoutSessionManagement $checkoutSessionManagement
     * @param Cart $cartHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AddressInterface $address,
        Region $region,
        ShippingInformationManagementInterface $shippingInformationManagement,
        ShippingInformationInterface $shippingInformation,
        AmazonPayAdapter $amazonPayAdapter,
        CheckoutSessionManagement $checkoutSessionManagement,
        Cart $cartHelper
    )
    {
        $this->cartRepository = $cartRepository;
        $this->address = $address;
        $this->region = $region;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformation = $shippingInformation;
        $this->checkoutSessionManager = $checkoutSessionManagement;
        $this->amazonPayAdapter = $amazonPayAdapter;
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
            $amazonSession = $this->amazonPayAdapter->getCheckoutSession($quote->getStoreId(), $checkoutSessionId);

            $amazonSessionStatus = $amazonSession['status'] ?? '404';
            if (!preg_match('/^2\d\d$/', $amazonSessionStatus)) {
                throw new WebapiException(
                    new Phrase($amazonSession['reasonCode'])
                );
            }

            $magentoAddress = $this->checkoutSessionManager->getShippingAddress($checkoutSessionId);
            $shippingAddress = $this->address->setData($magentoAddress[0]);
            $magentoAddress = $this->checkoutSessionManager->getBillingAddress($checkoutSessionId);
            $billingAddress = $this->address->setData($magentoAddress[0]);

            $quote->setShippingAddress($shippingAddress);
            $quote->setBillingAddress($billingAddress);
        }

        // Save and create response
        return $this->cartHelper->saveAndCreateResponse($quote, $checkoutSessionId);
    }
}
