<?php
/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
namespace Amazon\Payment\Helper;

use Amazon\Core\Helper\Data as AmazonCoreHelper;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order;
use Magento\Framework\Mail\Template\TransportBuilderFactory;
use Magento\Store\Model\ScopeInterface;

class Email extends AbstractHelper
{
    /**
     * @var TransportBuilderFactory
     */
    private $emailTransportBuilderFactory;

    /**
     * @var AmazonCoreHelper
     */
    private $amazonCoreHelper;

    /**
     * @param Context                 $context
     * @param TransportBuilderFactory $emailTransportBuilderFactory
     * @param AmazonCoreHelper        $amazonCoreHelper
     */
    public function __construct(
        Context                 $context,
        TransportBuilderFactory $emailTransportBuilderFactory,
        AmazonCoreHelper        $amazonCoreHelper
    ) {
        parent::__construct($context);
        $this->emailTransportBuilderFactory = $emailTransportBuilderFactory;
        $this->amazonCoreHelper = $amazonCoreHelper;
    }

    /**
     * @param Order $order
     *
     * @return void
     */
    public function sendAuthorizationSoftDeclinedEmail(Order $order)
    {
        $emailTransportBuilder = $this->emailTransportBuilderFactory->create();

        $emailTransportBuilder->addTo($order->getCustomerEmail(), $order->getCustomerName());
        $emailTransportBuilder->setFrom('general');
        $emailTransportBuilder->setTemplateIdentifier('amazon_payments_auth_soft_decline');
        $emailTransportBuilder->setTemplateOptions(
            [
                'area'  => Area::AREA_FRONTEND,
                'store' => $order->getStoreId()
            ]
        );

        $paymentRegionByOrderStore = $this->amazonCoreHelper->getPaymentRegion(
            ScopeInterface::SCOPE_STORE,
            $order->getStoreId()
        );

        $storeName = $this->amazonCoreHelper->getStoreName(ScopeInterface::SCOPE_STORE, $order->getStoreId());
        if (!$storeName) {
            $storeName = $this->amazonCoreHelper->getStoreFrontName($order->getStoreId());
        }

        $vars = [
            'amazonAccountUrl' => $this->amazonCoreHelper
                                       ->getAmazonAccountUrlByPaymentRegion($paymentRegionByOrderStore),
            'storeName' => $storeName,
        ];

        $emailTransportBuilder->setTemplateVars($vars);

        $emailTransportBuilder->getTransport()->sendMessage();
    }

    /**
     * @param Order $order
     *
     * @return void
     */
    public function sendAuthorizationHardDeclinedEmail(Order $order)
    {
        $emailTransportBuilder = $this->emailTransportBuilderFactory->create();

        $storeName = $this->amazonCoreHelper->getStoreName(ScopeInterface::SCOPE_STORE, $order->getStoreId());
        if (!$storeName) {
            $storeName = $this->amazonCoreHelper->getStoreFrontName($order->getStoreId());
        }

        $emailTransportBuilder->addTo($order->getCustomerEmail(), $order->getCustomerName());
        $emailTransportBuilder->setFrom('general');
        $emailTransportBuilder->setTemplateIdentifier('amazon_payments_auth_hard_decline');
        $emailTransportBuilder->setTemplateOptions(
            [
                'area'  => Area::AREA_FRONTEND,
                'store' => $order->getStoreId()
            ]
        );

        $vars = [
            'storeName' => $storeName,
        ];

        $emailTransportBuilder->setTemplateVars($vars);
        $emailTransportBuilder->getTransport()->sendMessage();
    }
}
