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
namespace Amazon\Payment\Model;

use Amazon\Payment\Api\Data\QuoteLinkInterface;
use Amazon\Payment\Model\ResourceModel\QuoteLink as QuoteLinkResourceModel;
use Magento\Framework\Model\AbstractModel;

class QuoteLink extends AbstractModel implements QuoteLinkInterface
{
    protected function _construct()
    {
        $this->_init(QuoteLinkResourceModel::class);
    }

    /**
     * {@inheritDoc}
     */
    public function setAmazonOrderReferenceId($amazonOrderReferenceId)
    {
        return $this->setData('amazon_order_reference_id', $amazonOrderReferenceId);
    }

    /**
     * {@inheritDoc}
     */
    public function getAmazonOrderReferenceId()
    {
        return $this->getData('amazon_order_reference_id');
    }

    /**
     * {@inheritDoc}
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData('quote_id', $quoteId);
    }

    /**
     * {@inheritDoc}
     */
    public function getQuoteId()
    {
        return $this->getData('quote_id');
    }

    /**
     * {@inheritDoc}
     */
    public function setSandboxSimulationReference($sandboxSimulationReference)
    {
        return $this->setData('sandbox_simulation_reference', $sandboxSimulationReference);
    }

    /**
     * {@inheritDoc}
     */
    public function getSandboxSimulationReference()
    {
        return $this->getData('sandbox_simulation_reference');
    }

    /**
     * {@inheritDoc}
     */
    public function setConfirmed($confirmed)
    {
        return $this->setData('confirmed', $confirmed);
    }

    /**
     * {@inheritDoc}
     */
    public function isConfirmed()
    {
        return (bool) $this->getData('confirmed');
    }
}
