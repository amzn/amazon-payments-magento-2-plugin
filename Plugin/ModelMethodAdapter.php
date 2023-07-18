<?php

namespace Amazon\Pay\Plugin;

use Amazon\Pay\Gateway\Config\Config;
use Amazon\Pay\Model\Config\Source\AuthorizationMode;
use Amazon\Pay\Model\Config\Source\PaymentAction;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ModelMethodAdapter
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ModelMethodAdapter constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Set Magento payment action
     *
     * If Amazon auth mode is Immediate, always treat as an authorize for Magento instead of Authorize and Capture.
     *
     * @param \Magento\Payment\Model\Method\Adapter $subject
     * @param mixed $result
     * @return string
     */
    public function afterGetConfigPaymentAction(\Magento\Payment\Model\Method\Adapter $subject, $result)
    {
        if ($subject->getCode() == Config::CODE) {
            if ($this->scopeConfig->getValue('payment/amazon_payment/authorization_mode') == AuthorizationMode::SYNC) {
                $result = PaymentAction::AUTHORIZE;
            }
        }

        return $result;
    }
}
