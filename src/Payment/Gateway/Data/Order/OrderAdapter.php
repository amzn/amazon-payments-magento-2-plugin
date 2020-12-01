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

namespace Amazon\Payment\Gateway\Data\Order;

use Magento\Payment\Gateway\Data\Order\AddressAdapterFactory;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Sales\Model\Order;
use Amazon\Core\Model\AmazonConfig;
use Amazon\Core\Helper\Data;

/**
 * Class OrderAdapter
 *
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class OrderAdapter implements OrderAdapterInterface
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var AddressAdapter
     */
    private $addressAdapterFactory;

    /**
     * @var Data
     */
    private $coreHelper;

    /**
     * @var AmazonConfig
     */
    private $config;

    /**
     * OrderAdapter constructor.
     *
     * @param Order $order
     * @param AddressAdapterFactory $addressAdapterFactory
     * @param Data $coreHelper
     * @param \Amazon\Core\Model\AmazonConfig $config
     */
    public function __construct(
        Order $order,
        \Magento\Payment\Gateway\Data\Order\AddressAdapterFactory $addressAdapterFactory,
        Data $coreHelper,
        AmazonConfig $config
    ) {
        $this->order = $order;
        $this->addressAdapterFactory = $addressAdapterFactory;
        $this->coreHelper = $coreHelper;
        $this->config = $config;
    }

    /**
     * Returns currency code
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->order->getBaseCurrencyCode();
    }

    /**
     * Returns order increment id
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->order->getIncrementId();
    }

    /**
     * Returns customer ID
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->order->getCustomerId();
    }

    /**
     * Returns billing address
     *
     * @return AddressAdapterInterface|null
     */
    public function getBillingAddress()
    {
        if ($this->order->getBillingAddress()) {
            return $this->addressAdapterFactory->create(
                ['address' => $this->order->getBillingAddress()]
            );
        }

        return null;
    }

    /**
     * Returns shipping address
     *
     * @return AddressAdapterInterface|null
     */
    public function getShippingAddress()
    {
        if ($this->order->getShippingAddress()) {
            return $this->addressAdapterFactory->create(
                ['address' => $this->order->getShippingAddress()]
            );
        }

        return null;
    }

    /**
     * Returns order store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->order->getStoreId();
    }

    /**
     * Returns order id
     *
     * @return int
     */
    public function getId()
    {
        return $this->order->getEntityId();
    }

    /**
     * Returns order grand total amount
     *
     * @return float|null
     */
    public function getGrandTotalAmount()
    {
        return $this->order->getBaseGrandTotal();
    }

    /**
     * Returns list of line items in the cart
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    public function getItems()
    {
        return $this->order->getItems();
    }

    /**
     * Gets the remote IP address for the order.
     *
     * @return string|null Remote IP address.
     */
    public function getRemoteIp()
    {
        return $this->order->getRemoteIp();
    }

    /**
     * Gets order currency code and amount if Amazon multi-currency was used.
     * @param $amount
     * @return array
     */
    public function getMulticurrencyDetails($amount)
    {
        $values = ['multicurrency' => false];

        if ($this->config->useMultiCurrency()) {
            $invoices = $this->order->getInvoiceCollection();

            foreach ($invoices->getItems() as $key => $invoice) {
                $baseTotal = $invoice->getBaseGrandTotal();

                // compare numeric values to make sure we have the right invoice
                // (could have an invoice for each item during partial capture).
                if (bccomp($baseTotal, (float)$amount) == 0) {
                    $values = [
                        'multicurrency' => true,
                        'order_currency' => $invoice->getOrderCurrencyCode(),
                        'total' => $invoice->getGrandTotal()
                    ];
                    break;
                }
            }
        }

        $values['store_name'] = $this->order->getStoreName();
        $values['store_id'] = $this->order->getStoreId();

        return $values;
    }


    /**
     * Returns current Amazon Order Reference ID
     * @return string
     */
    public function getAmazonOrderID()
    {
        $orderID = '';
        if (!empty($this->order->getExtensionAttributes()->getAmazonOrderReferenceId())) {
            $orderID = $this->order->getExtensionAttributes()->getAmazonOrderReferenceId()->getAmazonOrderReferenceId();
        }

        return $orderID;
    }
}
