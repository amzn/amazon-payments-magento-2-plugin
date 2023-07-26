<?php
/**
 * Copyright 2020 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
namespace Amazon\Pay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Email extends AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Amazon\Pay\Logger\AsyncIpnLogger
     */
    private $asyncLogger;

    /**
     * Email constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Mail\Template\TransportBuilderTransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Amazon\Pay\Logger\AsyncIpnLogger $asyncLogger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amazon\Pay\Logger\AsyncIpnLogger $asyncLogger
    ) {
        parent::__construct($context);
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->asyncLogger = $asyncLogger;
    }

    /**
     * Send email to customer when payment is asynchronously declined
     *
     * @param Order $order
     * @return void
     */
    public function sendPaymentDeclinedEmail(\Magento\Sales\Model\Order $order)
    {
        try {
            $storeName = $this->getStoreName($order->getStoreId());
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('amazon_pay_payment_declined')
                ->setTemplateOptions(
                    [
                        'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $order->getStoreId()
                    ]
                )
                ->setFrom('general')
                ->setTemplateVars(['storeName' => $storeName, 'incrementId' => $order->getIncrementId()])
                ->addTo($order->getCustomerEmail(), $order->getCustomerName())
                ->getTransport();

            $transport->sendMessage();
            $this->asyncLogger->info('Payment declined email sent for Order #' . $order->getIncrementId());
        } catch (\Exception $e) {
            $error = $order->getIncrementId() . '-' . $e->getMessage();
            $this->asyncLogger->info('Cannot send payment declined email for Order #' . $error);
        }
    }

    /**
     * Get displayed store name
     *
     * @param mixed $storeId
     * @return string
     */
    protected function getStoreName($storeId)
    {
        $store = $this->storeManager->getStore($storeId);
        return $store->getFrontendName();
    }
}
