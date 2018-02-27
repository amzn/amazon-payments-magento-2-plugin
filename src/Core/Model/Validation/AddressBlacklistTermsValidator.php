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

class AddressBlacklistTermsValidator extends AbstractValidator
{
    /**
     * @var Data
     */
    private $amazonCoreHelper;

    /**
     * @param Data $amazonCoreHelper
     */
    public function __construct(Data $amazonCoreHelper)
    {
        $this->amazonCoreHelper = $amazonCoreHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($entity)
    {
        if (!$this->amazonCoreHelper->isBlacklistedTermValidationEnabled()) {
            return true;
        }

        /** @var $entity \Magento\Customer\Api\Data\AddressInterface */
        $addressLines = (array) $entity->getStreet();

        foreach ($this->amazonCoreHelper->getBlackListedTerms() as $term) {
            foreach ($addressLines as $addressLine) {
                if (stripos($addressLine, $term) !== false) {
                    $this->_addMessages(['Unfortunately, we don’t deliver to lockers/packing stations.']);
                    return false;
                }
            }
        }

        return true;
    }
}
