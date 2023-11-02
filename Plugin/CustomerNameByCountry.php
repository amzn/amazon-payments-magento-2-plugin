<?php

namespace Amazon\Pay\Plugin;

use Magento\Customer\Model\Address\AddressModelInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\Resolver;

class CustomerNameByCountry
{
    protected const JAPANESE_JAPAN_LOCALE = 'ja_JP';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var Resolver
     */
    protected $store;

    /**
     * Plugin constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $eavConfig
     * @param Resolver $store
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $eavConfig,
        Resolver $store
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->eavConfig = $eavConfig;
        $this->store = $store;
    }

    /**
     * Rearrange name provided for Japanese Locale
     * before <prefix> <firstname> <middlename> <lastname> <suffix>
     * after <prefix> <lastname> <firstname> <middlename> <suffix>
     *
     * @param AddressModelInterface $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetName($subject, $result)
    {
        $locale = $this->store->getLocale();
        if ($locale != self::JAPANESE_JAPAN_LOCALE) {
            return $result;
        }
        $name = '';
        if ($this->eavConfig->getAttribute('customer_address', 'prefix')->getIsVisible() && $subject->getPrefix()) {
            $name .= $subject->getPrefix() . ' ';
        }
        $name .= $subject->getLastname();
        $name .=  ' ' . $subject->getFirstname();
        $middleName = $this->eavConfig->getAttribute('customer_address', 'middlename');
        if ($middleName->getIsVisible() && $subject->getMiddlename()) {
            $name .= ' ' . $subject->getMiddlename();
        }
        if ($this->eavConfig->getAttribute('customer_address', 'suffix')->getIsVisible() && $subject->getSuffix()) {
            $name .= ' ' . $subject->getSuffix();
        }
        return $name;
    }
}
