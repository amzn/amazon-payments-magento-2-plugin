<?php

namespace Amazon\PayV2\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Checkout\Helper\Data
     */
    private $helperCheckout;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;

    public function __construct(
        \Magento\Checkout\Helper\Data $helperCheckout,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\App\Helper\Context $context
    )
    {
        $this->helperCheckout = $helperCheckout;
        $this->moduleList = $moduleList;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isPayOnly()
    {
        $quote = $this->helperCheckout->getQuote();
        return $quote->hasItems() ? $quote->isVirtual() : true;
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
