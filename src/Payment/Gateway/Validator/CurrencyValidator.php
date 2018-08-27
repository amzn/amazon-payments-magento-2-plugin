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

namespace Amazon\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Amazon\Core\Helper\Data;

/**
 * Class CurrencyValidator
 * Validates allowable currencies for Amazon Pay
 */
class CurrencyValidator extends AbstractValidator
{

    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    private $config;

    /**
     * @var Data
     */
    private $coreHelper;

    /**
     * CurrencyValidator constructor.
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param ConfigInterface        $config
     * @param Data                   $coreHelper
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ConfigInterface $config,
        Data $coreHelper
    ) {
        $this->coreHelper = $coreHelper;
        $this->config = $config;
        parent::__construct($resultFactory);
    }

    /**
     * @param array $validationSubject
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {

        $allowedCurrency = $this->coreHelper->getCurrencyCode('store', $validationSubject['storeId']);

        if ($allowedCurrency == $validationSubject['currency']) {
            return $this->createResult(
                true,
                ['status' => 200]
            );
        }

        return $this->createResult(
            false,
            [__('The currency selected is not supported by Amazon Pay.')]
        );
    }
}
