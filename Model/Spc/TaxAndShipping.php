<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\SpcTaxAndShippingInterface;
use Amazon\Pay\Helper\SpcCart;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Directory\Model\Region;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Webapi\Exception as WebapiException;

class TaxAndShipping implements SpcTaxAndShippingInterface
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
     * @var SpcCart
     */
    protected $cartHelper;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param AddressInterface $address
     * @param Region $region
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param ShippingInformationInterface $shippingInformation
     * @param SpcCart $cartHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AddressInterface $address,
        Region $region,
        ShippingInformationManagementInterface $shippingInformationManagement,
        ShippingInformationInterface $shippingInformation,
        SpcCart $cartHelper
    )
    {
        $this->cartRepository = $cartRepository;
        $this->address = $address;
        $this->region = $region;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformation = $shippingInformation;
        $this->cartHelper = $cartHelper;
    }

    /**
     * @inheritdoc
     */
    public function calculateTaxAndShipping(int $cartId, $shippingDetails = null, $cartDetails = null)
    {
        try {
            $quote = $this->cartRepository->get($cartId);
        } catch (NoSuchEntityException $e) {
            throw new WebapiException(
                new Phrase($e->getMessage())
            );
        }

        if ($shippingDetails) {
            // Country, region, post code
            if (isset($shippingDetails['country'])
                && isset($shippingDetails['region'])
                && isset($shippingDetails['zipcode'])) {
                $country = $shippingDetails['country'];

                $regionCode = $shippingDetails['region'];
                $postCode = $shippingDetails['zipcode'];
            }
            else {
                throw new WebapiException(
                    new Phrase('Region, Zip Code, and Country are required.')
                );
            }

            // Street
            if (isset($shippingDetails['street'])) {
                $street = $shippingDetails['street'];
            }
            else {
                $street = null;
            }

            // City
            if (isset($shippingDetails['city'])) {
                $city = $shippingDetails['city'];
            }

            // Set main address details
            $address = $this->address->setStreet($street)
                ->setCity($city)
                ->setPostcode($postCode)
                ->setCountryId($country)
            ;

            $region = $this->region->loadByCode($regionCode, $country);
            if ($region->getId()) {
                $address->setRegion($region->getCode())
                    ->setRegionId($region->getId());
            }
            else {
                $address->setRegion($shippingDetails['region']);
            }

            // Phone
            if (isset($shippingDetails['phone'])) {
                $address->setTelephone($shippingDetails['phone']);
            }

            // Email
            if (isset($shippingDetails['email'])) {
                $address->setEmail($shippingDetails['email']);
                $quote->setCustomerEmail($shippingDetails['email']);
            }

            // Save address with shipping method
            if (isset($shippingDetails['shipping_method'])
                && (strpos($shippingDetails['shipping_method'], '_') !== false)) {
                $shippingInformation = $this->shippingInformation->setShippingAddress($address);

                $shippingMethod = explode('_', $shippingDetails['shipping_method']);
                $shippingInformation->setShippingCarrierCode($shippingMethod[0])
                    ->setShippingMethodCode($shippingMethod[1]);

                $this->shippingInformationManagement->saveAddressInformation($cartId, $shippingInformation);
            }
            // Save address without shipping method
            else {
                $quote->setShippingAddress($address);
            }
        }

        // Update item details
        if ($cartDetails) {
            $isQuoteItem = false;

            foreach ($cartDetails as $detail) {
                foreach ($quote->getAllVisibleItems() as $item) {
                    if ($item->getId() == $detail['id']) {
                        $item->setQty($detail['quantity']);

                        if ($item->getHasError()) {
                            throw new LocalizedException(__($item->getMessage()));
                        }

                        $isQuoteItem = true;
                        break;
                    }
                }
            }

            if (!$isQuoteItem) {
                throw new WebapiException(
                    new Phrase('This item(s) is not part of this cart.')
                );
            }
        }

        // Save and create response
        return $this->cartHelper->saveAndCreateResponse($quote);
    }
}
