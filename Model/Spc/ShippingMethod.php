<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\ShippingMethodInterface;
use Amazon\Pay\Helper\Spc\Cart;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
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
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param ShippingInformationInterface $shippingInformation
     * @param Cart $cartHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ShippingInformationManagementInterface $shippingInformationManagement,
        ShippingInformationInterface $shippingInformation,
        Cart $cartHelper
    )
    {
        $this->cartRepository = $cartRepository;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformation = $shippingInformation;
        $this->cartHelper = $cartHelper;
    }

    /**
     * @inheritdoc
     */
    public function shippingMethod(int $cartId, $shippingMethod)
    {
        try {
            $quote = $this->cartRepository->get($cartId);
        } catch (NoSuchEntityException $e) {
            throw new WebapiException(
                new Phrase($e->getMessage())
            );
        }

        if ($shippingMethod) {
            $address = $quote->getShippingAddress();

            // Save address with shipping method
            if (isset($shippingMethod['shippingMethodCode'])
                && (strpos($shippingMethod['shippingMethodCode'], '_') !== false)) {
                $shippingInformation = $this->shippingInformation->setShippingAddress($address);

                $shippingMethod = explode('_', $shippingMethod['shippingMethodCode']);
                $shippingInformation->setShippingCarrierCode($shippingMethod[0])
                    ->setShippingMethodCode($shippingMethod[1]);

                $this->shippingInformationManagement->saveAddressInformation($cartId, $shippingInformation);
            }
        }

        // Construct response
        return $this->cartHelper->saveAndCreateResponse($quote);
    }
}
