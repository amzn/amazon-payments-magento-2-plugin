<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Amazon\PayV2\Block;

/**
 * Config
 *
 * @api
 *
 * Provides a block that displays links to available custom error logs in Amazon Pay admin/config section.
 */
class Config extends \Magento\Framework\View\Element\Template
{
    const LANG_DE = 'de_DE';
    const LANG_FR = 'fr_FR';
    const LANG_ES = 'es_ES';
    const LANG_IT = 'it_IT';
    const LANG_JA = 'ja_JP';
    const LANG_UK = 'en_GB';
    const LANG_US = 'en_US';

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $localeResolver;

    /**
     * @var \Amazon\PayV2\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Amazon\Core\Helper\CategoryExclusion
     */
    private $categoryExclusionHelper;

    /**
     * Config constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Amazon\PayV2\Model\AmazonConfig $amazonConfig
     * @param \Amazon\Core\Helper\CategoryExclusion $categoryExclusionHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Amazon\Core\Helper\CategoryExclusion $categoryExclusionHelper
    ) {
        parent::__construct($context);
        $this->localeResolver = $localeResolver;
        $this->amazonConfig = $amazonConfig;
        $this->categoryExclusionHelper = $categoryExclusionHelper;
    }

    /**
     * @return string
     */
    protected function getLanguage()
    {
        $paymentRegion = $this->amazonConfig->getRegion();
        @list($lang, $region) = explode('_', $this->localeResolver->getLocale());
        switch ($lang) {
            case 'de':
                $result = self::LANG_DE;
                break;
            case 'fr':
                $result = self::LANG_FR;
                break;
            case 'es':
                $result = self::LANG_ES;
                break;
            case 'it':
                $result = self::LANG_IT;
                break;
            case 'ja':
                $result = self::LANG_JA;
                break;
            case 'en':
                $result = $paymentRegion == 'us' ? self::LANG_US : self::LANG_UK;
                break;
        }
        if (!isset($result)) {
            switch ($paymentRegion) {
                case 'jp':
                    $result = self::LANG_JA;
                    break;
                case 'us':
                    $result = self::LANG_US;
                    break;
                default:
                    $result = self::LANG_UK;
                    break;
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        $config = [
            'merchantId'               => $this->amazonConfig->getMerchantId(),
            'region'                   => $this->amazonConfig->getRegion(),
            'currency'                 => $this->amazonConfig->getCurrencyCode(),
            'sandbox'                  => $this->amazonConfig->isSandboxEnabled(),
            'language'                 => $this->getLanguage(),
            'placement'                => 'Cart',
            'code'                     => \Amazon\PayV2\Gateway\Config\Config::CODE,
            'is_method_available'      => $this->amazonConfig->isPayButtonAvailableAsPaymentMethod(),
        ];

        return $config;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->amazonConfig->isEnabled();
    }
}
