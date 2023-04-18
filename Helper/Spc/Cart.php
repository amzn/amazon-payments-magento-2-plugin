<?php

namespace Amazon\Pay\Helper\Spc;

use Amazon\Pay\Api\Spc\Response\AmountInterface;
use Amazon\Pay\Api\Spc\Response\AmountInterfaceFactory;
use Amazon\Pay\Api\Spc\Response\CartDetailsInterface;
use Amazon\Pay\Api\Spc\Response\CartDetailsInterfaceFactory;
use Amazon\Pay\Api\Spc\Response\DeliveryOptionInterface;
use Amazon\Pay\Api\Spc\Response\DeliveryOptionInterfaceFactory;
use Amazon\Pay\Api\Spc\Response\LineItemInterface;
use Amazon\Pay\Api\Spc\Response\LineItemInterfaceFactory;
use Amazon\Pay\Api\Spc\Response\NameValueInterface;
use Amazon\Pay\Api\Spc\Response\NameValueInterfaceFactory;
use Amazon\Pay\Api\Spc\Response\PromoInterface;
use Amazon\Pay\Api\Spc\Response\PromoInterfaceFactory;
use Amazon\Pay\Api\Spc\Response\ShippingMethodInterface;
use Amazon\Pay\Api\Spc\Response\ShippingMethodInterfaceFactory;
use Amazon\Pay\Api\Spc\ResponseInterface;
use Amazon\Pay\Api\Spc\ResponseInterfaceFactory;
use Amazon\Pay\Logger\Logger;
use Amazon\Pay\Model\AmazonConfig;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection as SalesRuleCollection;
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
     * @var SalesRuleCollection
     */
    protected $ruleCollection;

    /**
     * @var CartDetailsInterfaceFactory
     */
    protected $cartDetailsFactory;

    /**
     * @var AmountInterfaceFactory
     */
    protected $amountFactory;

    /**
     * @var PromoInterfaceFactory
     */
    protected $promoFactory;

    /**
     * @var DeliveryOptionInterfaceFactory
     */
    protected $deliveryOptionFactory;

    /**
     * @var ShippingMethodInterfaceFactory
     */
    protected $shippingMethodFactory;

    /**
     * @var LineItemInterfaceFactory
     */
    protected $lineItemFactory;

    /**
     * @var NameValueInterfaceFactory
     */
    protected $nameValueFactory;

    /**
     * @var ResponseInterfaceFactory
     */
    protected $responseFactory;

    /**
     * @var AmazonConfig
     */
    protected $amazonConfig;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var Logger
     */
    protected $logger;


    /**
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param CartRepositoryInterface $cartRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param SalesRuleCollection $ruleCollection
     * @param CartDetailsInterfaceFactory $cartDetailsFactory
     * @param AmountInterfaceFactory $amountFactory
     * @param PromoInterfaceFactory $promoFactory
     * @param DeliveryOptionInterfaceFactory $deliveryOptionFactory
     * @param ShippingMethodInterfaceFactory $shippingMethodFactory
     * @param LineItemInterfaceFactory $lineItemFactory
     * @param NameValueInterfaceFactory $nameValueFactory
     * @param ResponseInterfaceFactory $responseFactory
     * @param AmazonConfig $amazonConfig
     * @param DataObjectProcessor $dataObjectProcessor
     * @param Logger $logger
     */
    public function __construct(
        ShippingMethodManagementInterface $shippingMethodManagement,
        CartRepositoryInterface $cartRepository,
        ScopeConfigInterface $scopeConfig,
        SalesRuleCollection $ruleCollection,
        CartDetailsInterfaceFactory $cartDetailsFactory,
        AmountInterfaceFactory $amountFactory,
        PromoInterfaceFactory $promoFactory,
        DeliveryOptionInterfaceFactory $deliveryOptionFactory,
        ShippingMethodInterfaceFactory $shippingMethodFactory,
        LineItemInterfaceFactory $lineItemFactory,
        NameValueInterfaceFactory $nameValueFactory,
        ResponseInterfaceFactory $responseFactory,
        AmazonConfig $amazonConfig,
        DataObjectProcessor $dataObjectProcessor,
        Logger $logger
    )
    {
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->cartRepository = $cartRepository;
        $this->scopeConfig = $scopeConfig;
        $this->ruleCollection = $ruleCollection;
        $this->cartDetailsFactory = $cartDetailsFactory;
        $this->amountFactory = $amountFactory;
        $this->promoFactory = $promoFactory;
        $this->deliveryOptionFactory = $deliveryOptionFactory;
        $this->shippingMethodFactory = $shippingMethodFactory;
        $this->lineItemFactory = $lineItemFactory;
        $this->nameValueFactory = $nameValueFactory;
        $this->responseFactory = $responseFactory;
        $this->amazonConfig = $amazonConfig;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->logger = $logger;
    }

    /**
     * @param $quoteId
     * @param $checkoutSessionId
     * @return ResponseInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function createResponse($quoteId, $checkoutSessionId = null)
    {
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $this->cartRepository->get($quoteId);

        $cartLanguage = $this->getCartLanguage($quote);
        $currencyCode = $quote->getQuoteCurrencyCode();

        // Get line items and total base amount
        $lineItemsResponse = $this->getLineItemsAndTotalBaseAmount($quote, $currencyCode);
        $lineItems = $lineItemsResponse['line_items'];
        $totalBaseAmount = $lineItemsResponse['total_base_amount'];
        $lineItemsTotalDiscounts = $lineItemsResponse['total_discount_amount'];

        // Get delivery options
        $deliveryOptions = $this->getDeliveryOptions($quote, $currencyCode, $lineItems);

        // Get applied coupons
        $coupons = $this->getCoupons($quote);

        // Total discount amount
        $totalDiscountAmount = $lineItemsTotalDiscounts + $quote->getShippingAddress()->getShippingDiscountAmount();

        // Create response object
        /** @var $cartDetails CartDetailsInterface */
        $cartDetails = $this->cartDetailsFactory->create();
        $cartDetails->setCartId($quote->getId())
            ->setLineItems($lineItems)
            ->setDeliveryOptions($deliveryOptions)
            ->setCoupons($coupons)
            ->setCartLanguage($cartLanguage)
            ->setTotalDiscountAmount($this->getAmountObject(
                $quote->getSubtotal() - $quote->getSubtotalWithDiscount(),
                $currencyCode
            ))
            ->setTotalShippingAmount(
                $this->getAmountObject(
                    $quote->getShippingAddress()->getShippingAmount(),
                    $currencyCode
                )
            )
            ->setTotalBaseAmount($this->getAmountObject($totalBaseAmount, $currencyCode))
            ->setTotalTaxAmount($this->getAmountObject($quote->getShippingAddress()->getTaxAmount(), $currencyCode))
            ->setTotalChargeAmount($this->getAmountObject($quote->getGrandTotal(), $currencyCode))
            ->setTotalDiscountAmount($this->getAmountObject($totalDiscountAmount, $currencyCode))
            ->setCheckoutSessionId($checkoutSessionId);

        /** @var ResponseInterface $response */
        $response = $this->responseFactory->create();

        // log response for debugging
        if ($this->amazonConfig->isLoggingEnabled()) {
            $this->logger->info(json_encode(
                $this->dataObjectProcessor->buildOutputDataArray(
                    $cartDetails,
                    \Amazon\Pay\Api\Spc\Response\CartDetailsInterface::class)
                )
            );
        }

        return $response->setCartDetails($cartDetails);
    }

    /**
     * @param $quote
     * @return mixed
     */
    protected function getCartLanguage($quote)
    {
        return $this->scopeConfig->getValue(
            'general/locale/code',
            ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );
    }

    /**
     * @param $quote
     * @param $currencyCode
     * @return array
     */
    protected function getLineItemsAndTotalBaseAmount($quote, $currencyCode)
    {
        $lineItems = [];
        $totalBaseAmount = 0;
        $totalDiscountAmount = 0;

        foreach ($quote->getAllVisibleItems() as $item) {
            /** @var LineItemInterface $lineItem */
            $lineItem = $this->lineItemFactory->create();

            $additionalAttributes = [];

            // check if item is configurable to send options
            if ($item->getProductType() == Configurable::TYPE_CODE) {
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

                if (isset($options['attributes_info'])) {
                    foreach ($options['attributes_info'] as $option) {
                        /** @var NameValueInterface $nameValue */
                        $nameValue = $this->nameValueFactory->create();
                        $nameValue->setName($option['label'])
                            ->setValue($option['value']);
                        $additionalAttributes[] = $nameValue;
                    }
                }
            }

            // check if item is configurable to send options
            if ($item->getProductType() == Configurable::TYPE_CODE) {
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

                if (isset($options['attributes_info'])) {
                    foreach ($options['attributes_info'] as $option) {
                        /** @var NameValueInterface $nameValue */
                        $nameValue = $this->nameValueFactory->create();
                        $nameValue->setName($option['label'])
                            ->setValue($option['value']);
                        $additionalAttributes[] = $nameValue;
                    }
                }
            }

            // Get item rule details
            $rulesNameOrCode = [];
            
            if ($quote->getAppliedRuleIds() && $item->getAppliedRuleIds()) {
                $quoteRules = explode(',', $quote->getAppliedRuleIds());
                $itemRules = explode(',', $item->getAppliedRuleIds());

                foreach ($itemRules as $key => $ruleId) {
                    // remove rule from list if not on the quote, as Magento does not remove them from items
                    if (!in_array($ruleId, $quoteRules)) {
                        unset($itemRules[$key]);
                    }
                }

                if (!empty($itemRules)) {
                    $itemRules = implode(',', $itemRules);
                    $rules = $this->ruleCollection->addFieldToFilter('rule_id', ['in' => $itemRules]);
                    foreach ($rules as $rule) {
                        /** @var PromoInterface $ruleResponse */
                        $ruleResponse = $this->promoFactory->create();
                        $ruleResponse->setCouponCode($rule->getCode() ?: '')
                            ->setDescription($rule->getName());

                        $rulesNameOrCode[] = $ruleResponse;
                    }
                }
            }

            $totalBaseAmount += $item->getRowTotal();
            $discountedAmount = $item->getRowTotal() - $item->getDiscountAmount();
            $totalDiscountAmount += $item->getDiscountAmount();

            $lineItem->setId($item->getId())
                ->setTitle($item->getName())
                ->setQuantity($item->getQty())
                ->setListPrice($this->getAmountObject($item->getRowTotal()/$item->getQty(), $currencyCode))
                ->setTotalListPrice($this->getAmountObject($item->getRowTotal(), $currencyCode))
                ->setDiscountedPrice($this->getAmountObject($discountedAmount, $currencyCode))
                ->setAppliedDiscounts($rulesNameOrCode)
                ->setAdditionalAttributes($additionalAttributes)
                ->setStatus(
                    $item->getProduct()->getExtensionAttributes()->getStockItem()->getIsInStock() ?
                        self::STATUS_AVAILABLE : self::STATUS_OUT_OF_STOCK
                )
                ->setTaxAmount([$this->getAmountObject($item->getTaxAmount(), $currencyCode)])
            ;

            $lineItems[] = $lineItem;
        }

        return [
            'line_items' => $lineItems,
            'total_base_amount' => $totalBaseAmount,
            'total_discount_amount' => $totalDiscountAmount
        ];
    }

    /**
     * @param $quote
     * @param $currencyCode
     * @param $lineItems
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    protected function getDeliveryOptions($quote, $currencyCode, &$lineItems)
    {
        $deliveryOptions = [];

        if ($quote->getShippingAddress()->validate()) {
            /** @var \Magento\Quote\Api\Data\ShippingMethodInterface[] $magentoShippingMethods */
            $magentoShippingMethods =
                $this->shippingMethodManagement->estimateByExtendedAddress($quote->getId(), $quote->getShippingAddress());

            $magentoShippingMethods = array_filter($magentoShippingMethods, function($m) {
                /** @var \Magento\Quote\Api\Data\ShippingMethodInterface $m */
                return $m->getAvailable();
            });

            foreach ($magentoShippingMethods as $magentoMethod) {
                /** @var DeliveryOptionInterface $deliveryOption */
                $deliveryOption = $this->deliveryOptionFactory->create();

                /** @var ShippingMethodInterface $shippingMethod */
                $shippingMethod = $this->shippingMethodFactory->create();

                // Get shipping method name
                if (!$magentoMethod->getCarrierTitle()) {
                    $name = $magentoMethod->getMethodTitle();
                }
                else {
                    $name = $magentoMethod->getCarrierTitle() . ' - ' . $magentoMethod->getMethodTitle();
                }

                $code = $magentoMethod->getCarrierCode() .'_'. $magentoMethod->getMethodCode();

                $shippingMethod->setShippingMethodName($name)
                    ->setShippingMethodCode($code);

                $discountedPrice = $magentoMethod->getAmount() - $quote->getShippingAddress()->getShippingDiscountAmount();
                $deliveryOption->setId($magentoMethod->getCarrierCode() .'_'. $magentoMethod->getMethodCode())
                    ->setPrice($this->getAmountObject($magentoMethod->getAmount(), $currencyCode))
                    ->setDiscountedPrice(
                        $this->getAmountObject($discountedPrice > 0 ? $discountedPrice : 0, $currencyCode)
                    )
                    ->setShippingMethod($shippingMethod)
                    ->setShippingEstimate([]);

                if ($quote->getShippingAddress()->getShippingMethod() == ($magentoMethod->getCarrierCode() .'_'. $magentoMethod->getMethodCode())) {
                    $deliveryOption->setIsDefault(true);
                }
                else {
                    $deliveryOption->setIsDefault(false);
                }


                $deliveryOptions[] = $deliveryOption;
            }

            // check if no methods for an already set address
            if (empty($magentoShippingMethods) && $quote->getShippingAddress()->validate()) {
                // loop through the item response to set their status as NOT_AVAILABLE_FOR_SHIPPING_ADDRESS
                foreach ($lineItems as &$item) {
                    $item->setStatus(self::STATUS_NOT_AVAILABLE_FOR_SHIPPING_ADDRESS);
                }
            }
        }

        return $deliveryOptions;
    }

    /**
     * @param $quote
     * @return array
     */
    protected function getCoupons($quote)
    {
        if ($quote->getAppliedRuleIds()) {
            $rulesNameOrCode = [];
            $rules = $this->ruleCollection->addFieldToFilter('rule_id', ['in' => $quote->getAppliedRuleIds()]);

            foreach ($rules as $rule) {
                /** @var PromoInterface $ruleResponse */
                $ruleResponse = $this->promoFactory->create();
                $ruleResponse->setCouponCode($rule->getCode() ?: '')
                    ->setDescription($rule->getName());

                $rulesNameOrCode[] = $ruleResponse;
            }

            return $rulesNameOrCode;
        }

        return [];
    }

    /**
     * @param $amount
     * @param $currencyCode
     * @return AmountInterface
     */
    protected function getAmountObject($amount, $currencyCode)
    {
        /** @var AmountInterface $object */
        $object = $this->amountFactory->create();

        return $object->setAmount($amount)->setCurrencyCode($currencyCode);
    }

    /**
     * @param $message
     * @param $context
     * @return void
     */
    public function logError($message, $context)
    {
        $this->logger->error($message, $context);
    }
}
