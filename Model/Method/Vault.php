<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amazon\Pay\Model\Method;


use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\ConfigFactoryInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Block\Form;
use Magento\Vault\Model\VaultPaymentInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Amazon\Pay\Model\AmazonConfig;


class Vault extends \Magento\Vault\Model\Method\Vault
{
    /**
     * @var MethodInterface
     */
    private $vaultProvider;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     * @param ConfigFactoryInterface $configFactory
     * @param ObjectManagerInterface $objectManager
     * @param MethodInterface $vaultProvider
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param Command\CommandManagerPoolInterface $commandManagerPool
     * @param PaymentTokenManagementInterface $tokenManagement
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param string $code
     * @param Json|null $jsonSerializer
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ConfigInterface $config,
        ConfigFactoryInterface $configFactory,
        ObjectManagerInterface $objectManager,
        MethodInterface $vaultProvider,
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        Command\CommandManagerPoolInterface $commandManagerPool,
        PaymentTokenManagementInterface $tokenManagement,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        $code,
        AmazonConfig $amazonConfig,
        Json $jsonSerializer = null
    ) {

        $this->amazonConfig = $amazonConfig;
        $this->vaultProvider = $vaultProvider;
        parent::__construct($config, 
                            $configFactory, 
                            $objectManager, 
                            $vaultProvider, 
                            $eventManager, 
                            $valueHandlerPool, 
                            $commandManagerPool,
                            $tokenManagement,
                            $paymentExtensionFactory,
                            $code,
                            $jsonSerializer);
    }


    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return $this->vaultProvider->isAvailable($quote)
            && $this->amazonConfig->isVaultEnabled();
    }
}
