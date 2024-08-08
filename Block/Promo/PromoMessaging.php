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
     * Simple Product Type id
     */
    private const SIMPLE_TYPE_ID = 'simple';

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
     * AP Merchant Id getter
     *
     * @return string
     */
    public function getMerchantId(): string
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
     * Get product from registry
     *
     * @return Product
     */
    private function getProduct(): Product
    {
        return $this->registry->registry('product');
    }

    /**
     * AP Payment Product Type
     *
     * @return mixed
     */
    public function getPaymentProductType(): mixed
    {
        return $this->scopeConfig->getValue('payment/amazon_payment_v2/promo_message_product_type');
    }

    /**
     * AP Promo Font Color getter
     *
     * @return mixed
     */
    public function getPromoFontColor(): mixed
    {
        return $this->scopeConfig->getValue('payment/amazon_payment_v2/promo_message_color');
    }

    /**
     * AP Promo Font Color getter
     *
     * @return mixed
     */
    public function getPromoFontSize(): mixed
    {
        return $this->scopeConfig->getValue('payment/amazon_payment_v2/promo_message_font_size');
    }
}
