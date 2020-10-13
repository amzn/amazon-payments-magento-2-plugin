<?php


namespace Amazon\PayV2\Plugin;


use Amazon\PayV2\Gateway\Config\Config;
use Amazon\PayV2\Model\Config\Source\AuthorizationMode;
use Amazon\PayV2\Model\Config\Source\PaymentAction;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ModelMethodAdapter
 * @package Amazon\PayV2\Plugin
 */
class ModelMethodAdapter
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ModelMethodAdapter constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Payment\Model\Method\Adapter $subject
     * @param $result
     * @return string
     */
    public function afterGetConfigPaymentAction(\Magento\Payment\Model\Method\Adapter $subject, $result)
    {
        if ($subject->getCode() == Config::CODE) {
            if ($this->scopeConfig->getValue('payment/amazon_payment/authorization_mode') == AuthorizationMode::SYNC_THEN_ASYNC) {
                $result = PaymentAction::AUTHORIZE;
            }
        }

        return $result;
    }
}
