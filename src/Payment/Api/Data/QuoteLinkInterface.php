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
namespace Amazon\Payment\Api\Data;

use Exception;

/**
 * @api
 */
interface QuoteLinkInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * Set amazon order reference id
     *
     * @param string $amazonOrderReferenceId
     *
     * @return $this
     */
    public function setAmazonOrderReferenceId($amazonOrderReferenceId);

    /**
     * Get amazon order reference id
     *
     * @return string
     */
    public function getAmazonOrderReferenceId();

    /**
     * Set quote id
     *
     * @param integer $quoteId
     *
     * @return $this
     */
    public function setQuoteId($quoteId);

    /**
     * Get quote id
     *
     * @return integer
     */
    public function getQuoteId();

    /**
     * Set sandbox simulation reference
     *
     * @param string $sandboxSimulationReference
     *
     * @return $this
     */
    public function setSandboxSimulationReference($sandboxSimulationReference);

    /**
     * Get sandbox simulation reference
     *
     * @return string
     */
    public function getSandboxSimulationReference();

    /**
     * Set quote confirmed with amazon
     *
     * @param boolean $confirmed
     *
     * @return $this
     */
    public function setConfirmed($confirmed);

    /**
     * Get quote confirmed with amazon
     *
     * @return boolean
     */
    public function isConfirmed();

    /**
     * Save quote link
     *
     * @return $this
     * @throws Exception
     */
    public function save();

    /**
     * Delete quote link from database
     *
     * @return $this
     * @throws Exception
     */
    public function delete();

    /**
     * Load quote link data
     *
     * @param integer $modelId
     * @param null|string $field
     * @return $this
     */
    public function load($modelId, $field = null);
}
