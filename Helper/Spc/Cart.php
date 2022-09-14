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
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    protected $ruleCollection;

    /**
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param CartRepositoryInterface $cartRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\Collection $ruleCollection
     */
    public function __construct(
        ShippingMethodManagementInterface $shippingMethodManagement,
        CartRepositoryInterface $cartRepository,
        ScopeConfigInterface $scopeConfig,
        \Magento\SalesRule\Model\ResourceModel\Rule\Collection $ruleCollection
    )
    {
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->cartRepository = $cartRepository;
        $this->scopeConfig = $scopeConfig;
        $this->ruleCollection = $ruleCollection;
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

        // Loop to get item details
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

            // Get cart rule details
            $rules = $this->ruleCollection->addFieldToFilter('rule_id', ['in' => $item->getAppliedRuleIds()]);
            $rulesNameOrCode = [];
            foreach ($rules as $rule) {
                if ($rule->getCode()) {
                    $rulesNameOrCode[] = [
                        'couponCode' => $rule->getCode(),
                        'description' => $rule->getName(),
                    ];
                }
                else {
                    $rulesNameOrCode[] = [
                        'couponCode' => '',
                        'description' => $rule->getName(),
                    ];
                }
            }

            $lineItems[] = [
                'id' => (string)$item->getId(),
                'title' => $item->getName(),
                'quantity' => (string)$item->getQty(),
                'listPrice' => [
                    'amount' => (string)$item->getPrice(),
                    'currencyCode' => $currencyCode,
                ],
                'discountedPrice' => [
                    'amount' => (string)($item->getPrice() - $item->getDiscountAmount()),
                    'currencyCode' => $currencyCode,
                ],
                'appliedDiscounts' => $rulesNameOrCode,
                'additionalAttributes' => $additionalAttributes,
                'status' => $item->getProduct()->getExtensionAttributes()->getStockItem()->getIsInStock()
                    ? self::STATUS_AVAILABLE : self::STATUS_OUT_OF_STOCK,
                'taxAmount' => [
                    [
                        'amount' => (string)$item->getTaxAmount(),
                        'currencyCode' => $currencyCode,
                    ]
                ],
            ];
        }

        $methods = [];
        if ($quote->getShippingAddress()->validate()) {
            $shippingMethods = $this->shippingMethodManagement->getList($quote->getId());

            foreach ($shippingMethods as $method) {
                $methods[] = [
                    'id' => $method->getCarrierCode() .'_'. $method->getMethodCode(),
                    'price' => [
                        'amount' => (string)$method->getAmount(),
                        'currencyCode' => $currencyCode,
                    ],
                    'discountedPrice' => [],
                    'shippingMethod' => [
                        'shippingMethodName' => $method->getCarrierTitle() .' - '. $method->getMethodTitle(),
                        'shippingMethodCode' => $method->getCarrierCode() .'_'. $method->getMethodCode(),
                    ],
                    'shippingEstimate' => [],
                    'isDefault' =>
                        $quote->getShippingAddress()->getShippingMethod() == ($method->getCarrierCode() .'_'. $method->getMethodCode()),
                ];
            }

            // check if no methods for an already set address
            if (empty($shippingMethods) && $quote->getShippingAddress()->validate()) {
                // loop through the item response to set their status as NOT_AVAILABLE_FOR_SHIPPING_ADDRESS
                foreach ($lineItems as &$item) {
                    $item['status'] = self::STATUS_NOT_AVAILABLE_FOR_SHIPPING_ADDRESS;
                }
            }
        }

        // coupon code description
        $coupons = [];
        if ($quote->getCouponCode()) {
            $rule = $this->ruleCollection->addFieldToFilter('code', $quote->getCouponCode())->getFirstItem();
            $couponCodeDescription = $rule->getName();

            $coupons = [
                [
                    'couponCode' => $quote->getCouponCode(),
                    'description' => $couponCodeDescription
                ]
            ];
        }

        return [
            [
                'cartDetails' => [
                    'cartId' => $quote->getId(),
                    'lineItems' => $lineItems,
                    'deliveryOptions' => $methods,
                    'coupons' => $coupons,
                    'cartLanguage' => $storeLocale,
                    'totalShippingAmount' => [
                        'amount' => (string)$quote->getShippingAddress()->getShippingAmount(),
                        'currencyCode' => $currencyCode,
                    ],
                    'totalBaseAmount' => [
                        'amount' => (string)$quote->getSubtotal(),
                        'currencyCode' => $currencyCode,
                    ],
                    'totalTaxAmount' => [
                        'amount' => (string)$quote->getShippingAddress()->getTaxAmount(),
                        'currencyCode' => $currencyCode,
                    ],
                    'totalChargeAmount' => [
                        'amount' => (string)$quote->getGrandTotal(),
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
