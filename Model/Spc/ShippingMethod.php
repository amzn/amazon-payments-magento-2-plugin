<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\ShippingMethodInterface;
use Amazon\Pay\Helper\Spc\Cart;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Quote\Api\CartRepositoryInterface;

class ShippingMethod implements ShippingMethodInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var AmazonPayAdapter
     */
    protected $amazonPayAdapter;

    /**
     * @var ShippingInformationManagementInterface
     */
    protected $shippingInformationManagement;

    /**
     * @var ShippingInformationInterface
     */
    protected $shippingInformation;

    /**
     * @var Cart
     */
    protected $cartHelper;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param AmazonPayAdapter $amazonPayAdapter
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param ShippingInformationInterface $shippingInformation
     * @param Cart $cartHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AmazonPayAdapter $amazonPayAdapter,
        ShippingInformationManagementInterface $shippingInformationManagement,
        ShippingInformationInterface $shippingInformation,
        Cart $cartHelper
    )
    {
        $this->cartRepository = $cartRepository;
        $this->amazonPayAdapter = $amazonPayAdapter;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformation = $shippingInformation;
        $this->cartHelper = $cartHelper;
    }

    /**
     * @inheritdoc
     */
    public function shippingMethod(int $cartId, $cartDetails = null)
    {
        // Get quote
        $quote = $this->cartRepository->get($cartId);

        // Get checkoutSessionId
        $checkoutSessionId = $cartDetails['checkoutSessionId'] ?? null;

        // Check checkout session to
        if ($cartDetails && $checkoutSessionId) {
            $amazonSession = $this->amazonPayAdapter->getCheckoutSession($quote->getStoreId(), $checkoutSessionId);

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

            // Only grabbing the first one, as Magento only accepts one coupon code
            if (isset($cartDetails['deliveryOptions']['shippingMethod']['shippingMethodCode'])) {
                $shippingMethodCode = $cartDetails['deliveryOptions']['shippingMethod']['shippingMethodCode'];

                $address = $quote->getShippingAddress();

                // Save address with shipping method
                if ((strpos($shippingMethodCode, '_') !== false)) {
                    $shippingInformation = $this->shippingInformation->setShippingAddress($address);

                    $shippingMethod = explode('_', $shippingMethodCode);
                    $shippingInformation->setShippingCarrierCode($shippingMethod[0])
                        ->setShippingMethodCode($shippingMethod[1]);

                    $this->shippingInformationManagement->saveAddressInformation($cartId, $shippingInformation);
                }
            }
        }

        // Construct response
        return $this->cartHelper->saveAndCreateResponse($quote);
    }
}
