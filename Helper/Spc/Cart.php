<?php

namespace Amazon\Pay\Helper\Spc;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Store\Model\ScopeInterface;

class Cart
{
    const STATUS_AVAILABLE = 'AVAILABLE';
    const STATUS_OUT_OF_STOCK = 'OUT_OF_STOCK';
    const STATUS_NOT_AVAILABLE_FOR_SHIPPING_ADDRESS = 'NOT_AVAILABLE_FOR_SHIPPING_ADDRESS';

    /**
     * @var ShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param CartRepositoryInterface $cartRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ShippingMethodManagementInterface $shippingMethodManagement,
        CartRepositoryInterface $cartRepository,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->cartRepository = $cartRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $quote
     * @return \array[][]
     */
    public function createResponse($quote, $checkoutSessionId = null)
    {
        /** @var $quote \Magento\Quote\Model\Quote */

        $storeLocale = $this->scopeConfig->getValue(
            'general/locale/code',
            ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );
        $currencyCode = $quote->getQuoteCurrencyCode();
        $lineItems = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $additionalAttributes = [];

            if ($item->getWeight()) {
                $additionalAttributes[] = [
                    'name' => 'weight',
                    'value' => $item->getWeight(),
                ];
            }
            if ($item->getDescription()) {
                $additionalAttributes[] = [
                    'name' => 'description',
                    'value' => $item->getDescription(),
                ];
            }

            $lineItems[] = [
                'id' => $item->getId(),
                'title' => $item->getName(),
                'quantity' => $item->getQty(),
                'listPrice' => [
                    'amount' => $item->getPrice(),
                    'currencyCode' => $currencyCode,
                ],
                'discountedPrice' => [
                    'amount' => $item->getPrice() - $item->getDiscountAmount(),
                    'currencyCode' => $currencyCode,
                ],
                'appliedDiscounts' => [
                    $item->getAppliedRuleIds()
                ],
                'additionalAttributes' => $additionalAttributes,
                // the only reliable way to tell if it's taxable is if a tax has been calculated for it
                'taxable' => (boolean)$item->getTaxAmount(),
                'status' => $item->getProduct()->getExtensionAttributes()->getStockItem()->getIsInStock()
                    ? self::STATUS_AVAILABLE : self::STATUS_OUT_OF_STOCK,
                'taxAmount' => [
                    'amount' => $item->getTaxAmount(),
                    'currencyCode' => $currencyCode,
                ],
                'requiresShipping' => !(boolean)$item->getIsVirtual(),
            ];
        }

        $methods = [];
        if ($quote->getShippingAddress()->getPostCode()) {
            $shippingMethods = $this->shippingMethodManagement->getList($quote->getId());

            foreach ($shippingMethods as $method) {
                $methods[] = [
                    'id' => $method->getCarrierCode() .'_'. $method->getMethodCode(),
                    'price' => [
                        'amount' => $method->getAmount(),
                        'currencyCode' => $currencyCode,
                    ],
                    'discountedPrice' => [],
                    'shippingMethod' => [
                        'shippingMethodName' => $method->getCarrierTitle() .' - '. $method->getMethodTitle(),
                        'shippingMethodCode' => $method->getCarrierCode() .'_'. $method->getMethodCode(),
                    ],
                    'shippingEstimate' => [],
                    'selected' =>
                        $quote->getShippingAddress()->getShippingMethod() == ($method->getCarrierCode() .'_'. $method->getMethodCode()),
                ];
            }

            // check if no methods for an already set address
            if (empty($shippingMethods) && !$quote->getShippingAddress()->isEmpty()) {
                // loop through the item response to set their status as NOT_AVAILABLE_FOR_SHIPPING_ADDRESS
                foreach ($lineItems as &$item) {
                    $item['status'] = self::STATUS_NOT_AVAILABLE_FOR_SHIPPING_ADDRESS;
                }
            }
        }

        return [
            [
                'cartDetails' => [
                    'cartId' => $quote->getId(),
                    'lineItems' => $lineItems,
                    'deliveryOptions' => $methods,
                    'coupons' => [
                        [
                            'couponCode' => $quote->getCouponCode(),
                            'discountAmount' => $quote->getSubtotal() - $quote->getSubtotalWithDiscount()
                        ]
                    ],
                    'cartLanguage' => $storeLocale,
                    'totalShippingAmount' => [
                        'amount' => $quote->getShippingAddress()->getShippingAmount(),
                        'currencyCode' => $currencyCode,
                    ],
                    'totalBaseAmount' => [
                        'amount' => $quote->getSubtotal(),
                        'currencyCode' => $currencyCode,
                    ],
                    'totalTaxAmount' => [
                        'amount' => $quote->getShippingAddress()->getTaxAmount(),
                        'currencyCode' => $currencyCode,
                    ],
                    'totalChargeAmount' => [
                        'amount' => $quote->getGrandTotal(),
                        'currencyCode' => $currencyCode,
                    ],
                    'checkoutSessionId' => $checkoutSessionId
                ]
            ]
        ];
    }
    
    /**
     * @param $quote
     * @param $checkoutSessionId
     * @return \array[][]
     */
    public function saveAndCreateResponse($quote, $checkoutSessionId = null)
    {
        // Force the store to stay as the frontend one, so that the same endpoint can be used for all stores
        $storeId = $quote->getOrigData('store_id');
        $quote->setStoreId($storeId);

        // Collect totals
        $quote->collectTotals();

        // Save cart
        $this->cartRepository->save($quote);

        return $this->createResponse($quote, $checkoutSessionId);
    }
}
