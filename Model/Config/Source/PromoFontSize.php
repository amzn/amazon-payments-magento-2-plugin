<?php

namespace Amazon\Pay\Model\Config\Source;

class PromoFontSize implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * Font values available for promo message banner
     *
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('14px'),
                'value' => '14',
            ],
            [
                'label' => __('16px'),
                'value' => '16',
            ],
            [
                'label' => __('18px'),
                'value' => '18',
            ],
            [
                'label' => __('20px'),
                'value' => '20',
            ]
        ];
    }
}
