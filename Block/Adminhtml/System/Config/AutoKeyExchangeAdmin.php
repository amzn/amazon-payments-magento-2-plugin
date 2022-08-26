<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 */
namespace Amazon\Pay\Block\Adminhtml\System\Config;

class AutoKeyExchangeAdmin extends \Magento\Framework\View\Element\Template
{
    /**
     * @var AutoKeyExchange
     */
    private $autokeyexchange;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Amazon\Pay\Model\Config\AutoKeyExchange        $autokeyexchange
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amazon\Pay\Model\Config\AutoKeyExchange $autokeyexchange,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->autokeyexchange = $autokeyexchange;
    }

    /**
     * Return AutoKeyExchange settings
     */
    public function getJsonConfig()
    {
        return json_encode($this->autokeyexchange->getJsonAmazonAKEConfig());
    }

    /**
     * Return region
     */
    public function getRegion()
    {
        return $this->autokeyexchange->getRegion();
    }

    /**
     * Return currency
     */
    public function getCurrency()
    {
        $currency = $this->autokeyexchange->getCurrency();
        if ($currency) {
            $currency = strtoupper($currency);
        }
        return $currency;
    }
}
