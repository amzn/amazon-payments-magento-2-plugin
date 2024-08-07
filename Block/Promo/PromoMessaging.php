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
     * @param AmazonConfig $amazonConfig
     * @param Template\Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        private AmazonConfig $amazonConfig,
        Template\Context $context,
        private Registry $registry,
        private ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->amazonConfig->getMerchantId();
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->amazonConfig->getCurrencyCode();
    }

    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->amazonConfig->getLanguage();
    }

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->amazonConfig->isSandboxEnabled() ? 'sandbox' : 'live';
    }

    /**
     * @return float|null
     */
    public function getProductPrice(): ?float
    {
        $product = $this->getProduct();
        return $product ? $product->getPrice() : null;
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
     * @return mixed
     */
    public function getPromoFontColor(): mixed
    {
        return $this->scopeConfig->getValue('payment/amazon_payment_v2/promo_message_color');
    }

    /**
     * @return mixed
     */
    public function getPromoFontSize(): mixed
    {
        return $this->scopeConfig->getValue('payment/amazon_payment_v2/promo_message_font_size');
    }
}
