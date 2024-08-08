<?php

namespace Amazon\Pay\Model\Config\Source;

class PromoPaymentType implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * Payment "product type" checkout values available for promo message banner
     *
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('Pay And Ship'),
                'value' => 'PayAndShip',
            ],
            [
                'label' => __('Pay Only'),
                'value' => 'PayOnly',
            ],
            [
                'label' => __('Sign In'),
                'value' => 'SignIn',
            ]
        ];
    }
}
