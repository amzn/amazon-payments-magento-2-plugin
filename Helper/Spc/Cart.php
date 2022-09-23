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

    protected $responseFactory;

    /**
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param CartRepositoryInterface $cartRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\Collection $ruleCollection
     * @param CartDetailsInterfaceFactory $cartDetailsFactory
     * @param AmountInterfaceFactory $amountFactory
     * @param PromoInterfaceFactory $promoFactory
     * @param DeliveryOptionInterfaceFactory $deliveryOptionFactory
     * @param ShippingMethodInterfaceFactory $shippingMethodFactory
     * @param LineItemInterfaceFactory $lineItemFactory
     * @param NameValueInterfaceFactory $nameValueFactory
     */
    public function __construct(
        ShippingMethodManagementInterface $shippingMethodManagement,
        CartRepositoryInterface $cartRepository,
        ScopeConfigInterface $scopeConfig,
        \Magento\SalesRule\Model\ResourceModel\Rule\Collection $ruleCollection,
        CartDetailsInterfaceFactory $cartDetailsFactory,
        AmountInterfaceFactory $amountFactory,
        PromoInterfaceFactory $promoFactory,
        DeliveryOptionInterfaceFactory $deliveryOptionFactory,
        ShippingMethodInterfaceFactory $shippingMethodFactory,
        LineItemInterfaceFactory $lineItemFactory,
        NameValueInterfaceFactory $nameValueFactory,
        ResponseInterfaceFactory $responseFactory
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
    }

    /**
     * @param $quote
     * @return ResponseInterface
     */
    public function createResponse($quote, $checkoutSessionId = null)
    {
        /** @var $quote \Magento\Quote\Model\Quote */

        $cartLanguage = $this->getCartLanguage($quote);
        $currencyCode = $quote->getQuoteCurrencyCode();

        // Get line items and total base amount
        [$lineItems, $totalBaseAmount] = $this->getLineItemsAndTotalBaseAmount($quote, $currencyCode);

        // Get delivery options
        $deliveryOptions = $this->getDeliveryOptions($quote, $currencyCode, $lineItems);

        // Get applied coupons
        $coupons = $this->getCoupons($quote);

        // Create response object
        /** @var $cartDetails CartDetailsInterface */
        $cartDetails = $this->cartDetailsFactory->create();
        $cartDetails->setCartId($quote->getId())
            ->setLineItems($lineItems)
            ->setDeliveryOptions($deliveryOptions)
            ->setCoupons($coupons)
            ->setCartLanguage($cartLanguage)
            ->setTotalShippingAmount(
                $this->getAmountObject(
                    $quote->getShippingAddress()->getShippingAmount() - $quote->getShippingAddress()->getShippingDiscountAmount(),
                    $currencyCode
                )
            )
            ->setTotalBaseAmount($this->getAmountObject($totalBaseAmount, $currencyCode))
            ->setTotalTaxAmount($this->getAmountObject($quote->getShippingAddress()->getTaxAmount(), $currencyCode))
            ->setTotalChargeAmount($this->getAmountObject($quote->getGrandTotal(), $currencyCode))
            ->setCheckoutSessionId($checkoutSessionId);

        /** @var ResponseInterface $response */
        $response = $this->responseFactory->create();

        return $response->setCartDetails($cartDetails);
    }

    /**
     * @param $quote
     * @param $checkoutSessionId
     * @return ResponseInterface
     */
    public function saveAndCreateResponse($quote, $checkoutSessionId = null)
    {
        // Collect totals
        $quote->collectTotals();

        // Save cart
        $this->cartRepository->save($quote);

        return $this->createResponse($quote, $checkoutSessionId);
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

        foreach ($quote->getAllVisibleItems() as $item) {
            /** @var LineItemInterface $lineItem */
            $lineItem = $this->lineItemFactory->create();

            $additionalAttributes = [];
            if ($item->getWeight()) {
                /** @var NameValueInterface $nameValue */
                $nameValue = $this->nameValueFactory->create();
                $nameValue->setName('weight')
                    ->setValue($item->getWeight());
                $additionalAttributes[] = $nameValue;
            }
            if ($item->getDescription()) {
                /** @var NameValueInterface $nameValue */
                $nameValue = $this->nameValueFactory->create();
                $nameValue->setName('description')
                    ->setValue($item->getDescription());
                $additionalAttributes[] = $nameValue;
            }

            // Get cart rule details
            $rules = $this->ruleCollection->addFieldToFilter('rule_id', ['in' => $item->getAppliedRuleIds()]);
            $rulesNameOrCode = [];
            foreach ($rules as $rule) {
                /** @var PromoInterface $ruleResponse */
                $ruleResponse = $this->promoFactory->create();
                $ruleResponse->setCouponCode($rule->getCode() ?: '')
                    ->setDescription($rule->getName());

                $rulesNameOrCode[] = $ruleResponse;
            }

            $discountedAmount = $item->getPrice() - $item->getDiscountAmount()/$item->getQty();
            $totalBaseAmount += $discountedAmount * $item->getQty();
            $lineItem->setId($item->getId())
                ->setTitle($item->getName())
                ->setQuantity($item->getQty())
                ->setListPrice($this->getAmountObject($item->getPrice(), $currencyCode))
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

        return [$lineItems, $totalBaseAmount];
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
            $magentoShippingMethods =
                $this->shippingMethodManagement->estimateByExtendedAddress($quote->getId(), $quote->getShippingAddress());

            foreach ($magentoShippingMethods as $magentoMethod) {
                /** @var DeliveryOptionInterface $deliveryOption */
                $deliveryOption = $this->deliveryOptionFactory->create();

                /** @var ShippingMethodInterface $shippingMethod */
                $shippingMethod = $this->shippingMethodFactory->create();
                $shippingMethod->setShippingMethodName($magentoMethod->getCarrierTitle() .' - '. $magentoMethod->getMethodTitle())
                    ->setShippingMethodCode($magentoMethod->getCarrierCode() .'_'. $magentoMethod->getMethodCode());

                $discountedPrice = $magentoMethod->getAmount() - $quote->getShippingAddress()->getShippingDiscountAmount();
                $deliveryOption->setId($magentoMethod->getCarrierCode() .'_'. $magentoMethod->getMethodCode())
                    ->setPrice($this->getAmountObject($magentoMethod->getAmount(), $currencyCode))
                    ->setDiscountedPrice(
                        $this->getAmountObject($discountedPrice > 0 ? $discountedPrice : 0, $currencyCode)
                    )
                    ->setShippingMethod($shippingMethod)
                    ->setShippingEstimate([])
                    ->setIsDefault(
                        $quote->getShippingAddress()->getShippingMethod() == ($magentoMethod->getCarrierCode() .'_'. $magentoMethod->getMethodCode())
                    );

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
        if ($quote->getCouponCode()) {
            $rule = $this->ruleCollection->addFieldToFilter('code', $quote->getCouponCode())->getFirstItem();
            $couponCodeDescription = $rule->getName();

            /** @var PromoInterface $promo */
            $promo = $this->promoFactory->create();
            $promo->setCouponCode($quote->getCouponCode())
                ->setDescription($couponCodeDescription);

            return [$promo];
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
}
