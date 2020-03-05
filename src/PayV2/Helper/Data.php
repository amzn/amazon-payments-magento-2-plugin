<?php

namespace Amazon\PayV2\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Checkout\Helper\Data
     */
    private $helperCheckout;

    public function __construct(
        \Magento\Checkout\Helper\Data $helperCheckout,
        \Magento\Framework\App\Helper\Context $context
    )
    {
        $this->helperCheckout = $helperCheckout;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isPayOnly()
    {
        return $this->helperCheckout->getQuote()->isVirtual();
    }
}
