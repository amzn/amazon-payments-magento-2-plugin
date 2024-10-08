<?php

namespace Amazon\Pay\Block\Promo;

use Amazon\Pay\Model\AmazonConfig;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class PromoMessaging extends \Magento\Framework\View\Element\Template
{

    /**
     * These product types are not supported for promo messaging
     */
    protected const EXCLUDED_PRODUCT_TYPES = ['giftcard', 'grouped', 'virtual'];

    /**
     * @var AmazonConfig
     */
    protected $amazonConfig;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param AmazonConfig $amazonConfig
     * @param Template\Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        AmazonConfig $amazonConfig,
        Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->amazonConfig = $amazonConfig;
        $this->registry = $registry;
    }

    /**
     * Checks if promo messaging is enabled
     *
     * @return int
     */
    public function isPromoMessageEnabled(): int
    {
        return $this->_scopeConfig->getValue('payment/amazon_payment_v2/promo_message_enabled');
    }

    /**
     * AP Merchant Id getter
     *
     * @return string|null
     */
    public function getMerchantId(): ?string
    {
        return $this->amazonConfig->getMerchantId();
    }

    /**
     * AP Currency Code getter
     *
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->amazonConfig->getCurrencyCode();
    }

    /**
     * AP Language Code getter
     *
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->amazonConfig->getLanguage();
    }

    /**
     * Determines current environment based on if sandbox is enabled or not
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->amazonConfig->isSandboxEnabled() ? 'sandbox' : 'live';
    }

    /**
     * Get product from registry
     *
     * @return Product
     */
    private function getProduct(): Product
    {
        return $this->registry->registry('product');
    }

    /**
     * Grabs current product's price for promo vars
     *
     * @return float|null
     */
    public function getProductPrice(): ?float
    {
        $product = $this->getProduct();
        return $product ? $product->getPrice() : null;
    }

    /**
     * Checks if product is of a type that does not contain static price values
     *
     * @return bool
     */
    public function checkIsEligibleProduct(): bool
    {
        $product = $this->getProduct();
        return !in_array($product->getTypeId(), self::EXCLUDED_PRODUCT_TYPES, true);
    }

    /**
     * AP Payment Product Type
     *
     * @return string|null
     */
    public function getPaymentProductType(): ?string
    {
        return $this->_scopeConfig->getValue('payment/amazon_payment_v2/promo_message_product_type');
    }

    /**
     * AP Promo Font Color getter
     *
     * @return string|null
     */
    public function getPromoFontColor(): ?string
    {
        return $this->_scopeConfig->getValue('payment/amazon_payment_v2/promo_message_color');
    }

    /**
     * AP Promo Font Color getter
     *
     * @return string|null
     */
    public function getPromoFontSize(): ?string
    {
        return $this->_scopeConfig->getValue('payment/amazon_payment_v2/promo_message_font_size');
    }
}
