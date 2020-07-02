<?php

namespace Amazon\PayV2\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @var \Amazon\PayV2\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    private $helperCheckout;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;

    public function __construct(
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Magento\Checkout\Helper\Data $helperCheckout,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\App\Helper\Context $context
    )
    {
        $this->amazonConfig = $amazonConfig;
        $this->helperCheckout = $helperCheckout;
        $this->moduleList = $moduleList;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function isPayOnly($quote = null)
    {
        if ($quote === null) {
            $quote = $this->helperCheckout->getQuote();
        }
        return $quote->hasItems() ? $quote->isVirtual() : true;
    }

    /**
     * @param string $scope
     * @param string $scopeCode
     * @return boolean
     */
    public function isBillingAddressRequired($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $region = $this->amazonConfig->getPaymentRegion($scope, $scopeCode);
        return in_array($region, [
            'de',
            'uk',
        ]);
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        $module = $this->moduleList->getOne('Amazon_PayV2');
        return $module['setup_version'] ?? __('--');
    }
}
