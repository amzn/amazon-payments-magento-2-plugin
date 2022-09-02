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
use Magento\Quote\Api\ShippingMethodManagementInterface;

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
     * @var ShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @var Cart
     */
    protected $cartHelper;


    /**
     * @param CartRepositoryInterface $cartRepository
     * @param AmazonPayAdapter $amazonPayAdapter
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param ShippingInformationInterface $shippingInformation
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param Cart $cartHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AmazonPayAdapter $amazonPayAdapter,
        ShippingInformationManagementInterface $shippingInformationManagement,
        ShippingInformationInterface $shippingInformation,
        ShippingMethodManagementInterface $shippingMethodManagement,
        Cart $cartHelper
    )
    {
        $this->cartRepository = $cartRepository;
        $this->amazonPayAdapter = $amazonPayAdapter;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformation = $shippingInformation;
        $this->shippingMethodManagement = $shippingMethodManagement;
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
            if ($quote->getShippingAddress()->validate()
                && isset($cartDetails['deliveryOptions'][0]['shippingMethod']['shippingMethodCode'])) {
                $shippingMethodCode = $cartDetails['deliveryOptions'][0]['shippingMethod']['shippingMethodCode'];

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
            // Select the cheapest option
            else if ($quote->getShippingAddress()->validate()) {
                $shippingMethods = $this->shippingMethodManagement->getList($quote->getId());
                $cheapestMethod = [
                    'carrier' => '',
                    'code' => '',
                    'amount' => 100000
                ];

                foreach ($shippingMethods as $method) {
                    if ($method->getAmount() < $cheapestMethod['amount']) {
                        $cheapestMethod['carrier'] = $method->getCarrierCode();
                        $cheapestMethod['code'] = $method->getMethodCode();
                        $cheapestMethod['amount'] = $method->getAmount();
                    }
                }

                // Save address with shipping method
                if (!empty($cheapestMethod['carrier'])) {
                    $address = $quote->getShippingAddress();
                    $shippingInformation = $this->shippingInformation->setShippingAddress($address);

                    $shippingInformation->setShippingCarrierCode($cheapestMethod['carrier'])
                        ->setShippingMethodCode($cheapestMethod['code']);

                    $this->shippingInformationManagement->saveAddressInformation($cartId, $shippingInformation);
                }
            }
        }

        // Construct response
        return $this->cartHelper->saveAndCreateResponse($quote, $checkoutSessionId);
    }
}
