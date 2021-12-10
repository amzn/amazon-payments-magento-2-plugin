<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amazon\Pay\Block\Customer;

use Amazon\Pay\Model\AmazonConfig;
use Amazon\Pay\Model\Ui\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractTokenRenderer;

class VaultTokenRenderer extends AbstractTokenRenderer
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Initialize dependencies.
     *
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        AmazonConfig $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

     /**
     * @inheritdoc
     */
    public function getIconUrl()
    {
        return $this->config->getAmazonIcon()['url'];
    }

    /**
     * @inheritdoc
     */
    public function getIconHeight()
    {
        return $this->config->getAmazonIcon()['height'];
    }

    /**
     * @inheritdoc
     */
    public function getIconWidth()
    {
        return $this->config->getAmazonIcon()['width'];
    }


    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token): bool
    {
        return $token->getPaymentMethodCode() === ConfigProvider::CODE;
    }

    
    public function getExpDate(): string
    {
        $expDate = new \DateTime($this->getToken()->getExpiresAt());
        return $expDate->format('m/Y');
    }
}
