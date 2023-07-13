<?php

namespace Amazon\Pay\Helper\Spc;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Amazon\Pay\Logger\Logger;

class ShippingMethod
{
    const APPLIED = 'applied';
    const NOT_APPLIED = 'not_applied';

    /**
     * @var ShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @var ShippingInformationInterface
     */
    protected $shippingInformation;

    /**
     * @var ShippingInformationManagementInterface
     */
    protected $shippingInformationManagement;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param ShippingInformationInterface $shippingInformation
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param CartRepositoryInterface $cartRepository
     * @param Logger $logger
     */
    public function __construct(
        ShippingMethodManagementInterface $shippingMethodManagement,
        ShippingInformationInterface $shippingInformation,
        ShippingInformationManagementInterface $shippingInformationManagement,
        CartRepositoryInterface $cartRepository,
        Logger $logger
    )
    {
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->shippingInformation = $shippingInformation;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->cartRepository = $cartRepository;
        $this->logger = $logger;
    }

    /**
     * @param $quote
     * @param $code
     * @return string|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setShippingMethodOnQuote($quote, $code = false)
    {
        // Only grabbing the first one, as Magento only accepts one coupon code
        if ($quote->getShippingAddress()->validate() && $code) {
            $shippingMethodCode = $code;

            $shippingAddress = $quote->getShippingAddress();
            $billingAddress = $quote->getBillingAddress();

            // Save address with shipping method
            if ((strpos($shippingMethodCode, '_') !== false)) {
                $shippingInformation = $this->shippingInformation
                    ->setShippingAddress($shippingAddress)
                    ->setBillingAddress($billingAddress);

                // Separate the carrier from the method, the Magento way
                // https://github.com/magento/magento2/blob/2.4.5/app/code/Magento/Quote/Model/Quote/ShippingAssignment/ShippingProcessor.php#L68-L71
                $methodComponents = explode('_', $shippingMethodCode);
                $carrierCode = array_shift($methodComponents);
                $methodCode = implode('_', $methodComponents);

                // Set the carrier and method codes
                $shippingInformation->setShippingCarrierCode($carrierCode)
                    ->setShippingMethodCode($methodCode);

                try {
                    $this->shippingInformationManagement->saveAddressInformation($quote->getId(), $shippingInformation);

                    $refreshedQuote = $this->cartRepository->get($quote->getId());

                    if ($refreshedQuote->getShippingAddress()->getShippingMethod() == $shippingMethodCode)  {
                        return self::APPLIED;
                    }
                } catch (\Exception $e) {
                    $this->logger->info('SPC - Failed to apply shipping method '. $shippingMethodCode .' - cartId: '. $quote->getId());
                }
            }

            $this->setCheapestMethod($quote);

            return self::NOT_APPLIED;
        }
        // Select the cheapest option
        else if ($quote->getShippingAddress()->validate()) {
            $this->setCheapestMethod($quote);
        }
    }

    /**
     * @param $quote
     * @return void
     */
    protected function setCheapestMethod($quote)
    {
        /** @var \Magento\Quote\Api\Data\ShippingMethodInterface[] $shippingMethods */
        $shippingMethods = $this->shippingMethodManagement->estimateByExtendedAddress($quote->getId(), $quote->getShippingAddress());
        $cheapestMethod = [
            'carrier' => '',
            'code' => '',
            'amount' => 100000
        ];

        foreach ($shippingMethods as $method) {
            if ($method->getAvailable() && $method->getAmount() < $cheapestMethod['amount']) {
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

            try {
                $this->shippingInformationManagement->saveAddressInformation($quote->getId(), $shippingInformation);
            } catch (\Exception $e) {
                $this->logger->info('SPC - Failed to set the cheapest shipping method - cartId: '. $quote->getId());
            }
        }
    }
}
