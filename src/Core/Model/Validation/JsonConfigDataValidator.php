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
namespace Amazon\Core\Model\Validation;

use Amazon\Core\Helper\Data;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Json\DecoderInterface;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class JsonConfigDataValidator extends AbstractValidator
{
    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var Data
     */
    private $amazonCoreHelper;

    /**
     * @param DecoderInterface $jsonDecoder
     * @param Data $amazonCoreHelper
     */
    public function __construct(
        Data $amazonCoreHelper,
        DecoderInterface $jsonDecoder
    ) {
        $this->amazonCoreHelper = $amazonCoreHelper;
        $this->jsonDecoder      = $jsonDecoder;
    }

    /**
     * @param string $credentialsJson
     * @return bool
     */
    public function isValid($credentialsJson)
    {
        try {
            $decodedCredentials = $this->jsonDecoder->decode($credentialsJson);
        } catch (\Zend_Json_Exception $e) {
            $this->_addMessages(['Invalid Credentials JSON supplied! ' . $e->getMessage()]);
            return false;
        }

        if (!$this->mandatoryFieldsExist($decodedCredentials)) {
            $this->_addMessages(['Required fields are missing in supplied JSON!']);
            return false;
        }

        return true;
    }

    protected function mandatoryFieldsExist($decodedCredentials)
    {
        // SimplePath
        if (isset($decodedCredentials['encryptedKey'])) {
            return true;
        }

        foreach ($this->amazonCoreHelper->getAmazonCredentialsFields() as $mandatoryField) {
            if (!isset($decodedCredentials[$mandatoryField])) {
                return false;
            }
        }

        return true;
    }
}
