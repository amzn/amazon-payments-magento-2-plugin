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

namespace Amazon\Core\Block\Adminhtml\System\Config;

class SimplePathAdmin extends \Magento\Framework\View\Element\Template
{
    /**
     * @var SimplePath
     */
    private $simplePath;

    /**
     * SimplePathAdmin constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Amazon\Core\Model\Config\SimplePath             $simplePath
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amazon\Core\Model\Config\SimplePath $simplePath,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->simplePath = $simplePath;
    }

    /**
     * Return SimplePath settings
     */
    public function getJsonConfig()
    {
        return json_encode($this->simplePath->getJsonAmazonSpConfig());
    }

    /**
     * Return region
     */
    public function getRegion()
    {
        return $this->simplePath->getRegion();
    }

    /**
     * Return currency
     */
    public function getCurrency()
    {
        return $this->simplePath->getCurrency();
    }
}
