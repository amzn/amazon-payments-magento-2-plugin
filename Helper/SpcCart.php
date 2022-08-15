<?php

namespace Amazon\Pay\Helper;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;

class SpcCart
{
    /**
     * @var ShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        ShippingMethodManagementInterface $shippingMethodManagement,
        CartRepositoryInterface $cartRepository
    )
    {
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param $quote
     * @return \array[][]
     */
    public function createResponse($quote)
    {
        $itemsDetails = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $itemsDetails[] = [
                'id' => $item->getId(),
                'title' => $item->getName(),
                'quantity' => $item->getQty(),
                'basePrice' => $item->getBasePrice(),
                'taxAmount' => $item->getTaxAmount(),
                'discountAmount' => $item->getDiscountAmount(),
                'deliveryDetails' => [
                    'deliveryZipCode' => $quote->getShippingAddress()->getPostcode(),
                    'shippingMethod' => $quote->getShippingAddress()->getShippingMethod(),
                ],
            ];
        }

        $methods = [];
        if ($quote->getShippingAddress()->getPostCode()) {
            $shippingMethods = $this->shippingMethodManagement->getList($quote->getId());

            foreach ($shippingMethods as $method) {
                $methods[] = [
                    'code' => $method->getCarrierCode() .'_'. $method->getMethodCode(),
                    'carrier_title' => $method->getCarrierTitle(),
                    'method_title' => $method->getMethodTitle(),
                    'amount' => $method->getAmount(),
                    'selected' =>
                        $quote->getShippingAddress()->getShippingMethod() == ($method->getCarrierCode() .'_'. $method->getMethodCode()),
                ];
            }
        }

        return [
            [
                'cartDetails' => [
                    'cartId' => $quote->getId(),
                    'currencyCode' => $quote->getQuoteCurrencyCode(),
                    'itemsDetails' => $itemsDetails,
                    'couponDetails' => [
                        'couponCode' => $quote->getCouponCode(),
                        'discountAmount' => $quote->getSubtotal() - $quote->getSubtotalWithDiscount()
                    ],
                    'shippingMethods' => $methods,
                    'totalShippingAmount' => $quote->getShippingAddress()->getShippingAmount(),
                    'totalBaseAmount' => $quote->getSubtotal(),
                    'totalTaxAmount' => $quote->getShippingAddress()->getTaxAmount(),
                    'totalChargeAmount' => $quote->getGrandTotal(),
                ]
            ]
        ];
    }

    /**
     * @param $quote
     * @return \array[][]
     */
    public function saveAndCreateResponse($quote)
    {
        // Force the store to stay as the frontend one, so that the same endpoint can be used for all stores
        $storeId = $quote->getOrigData('store_id');
        $quote->setStoreId($storeId);

        // Collect totals
        $quote->collectTotals();

        // Save cart
        $this->cartRepository->save($quote);

        return $this->createResponse($quote);
    }
}
