<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
namespace Amazon\Pay\Block;

use Amazon\Pay\Model\AmazonConfig;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * @api
 */
class Login extends Template
{
    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * Login constructor.
     * @param Context $context
     * @param AmazonConfig $amazonConfig
     */
    public function __construct(
        Context $context,
        AmazonConfig $amazonConfig
    ) {
        $this->amazonConfig = $amazonConfig;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml()
    {
        if (!$this->amazonConfig->isLwaEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
