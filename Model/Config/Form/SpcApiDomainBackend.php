<?php

namespace Amazon\Pay\Model\Config\Form;

class SpcApiDomainBackend extends \Magento\Framework\App\Config\Value
{
    /**
     * Before save
     *
     * @return $this|SpcApiDomainBackend
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        if (!empty($value) && !preg_match("/^https:\/\/.*\/$/", $value)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The Buy Now domain does not match the format &quot;https://&lt;override-domain&gt;/&quot;')
            );
        }

        return $this;
    }
}
