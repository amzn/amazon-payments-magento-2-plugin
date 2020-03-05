<?php

namespace Amazon\PayV2\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Amazon\PayV2\Helper\Data
     */
    private $amazonHelper;

    /**
     * @param \Amazon\PayV2\Helper\Data $amazonHelper
     */
    public function __construct(\Amazon\PayV2\Helper\Data $amazonHelper)
    {
        $this->amazonHelper = $amazonHelper;
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return [
            'payment' => [
                \Amazon\PayV2\Gateway\Config\Config::CODE => [
                    'isPayOnly' => $this->amazonHelper->isPayOnly(),
                ],
            ],
        ];
    }
}
