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

namespace Amazon\Pay\Gateway\Http\Client;

use Amazon\Pay\Model\Config\Source\PaymentAction;

/**
 * Class AuthorizeClient
 * Amazon Pay authorization gateway client
 */
class AuthorizeSaleVaultClient extends AbstractClient
{
    /**
     * @var AmazonConfig
     */
    private $amazonConfig;
    /**
     * @inheritdoc
     */

    public function __construct(
        \Magento\Payment\Model\Method\Logger $logger,
        \Amazon\Pay\Model\Adapter\AmazonPayAdapter $adapter,
        \Amazon\Pay\Model\AmazonConfig $amazonConfig
    ) {
        parent::__construct($logger, $adapter);
        $this->amazonConfig = $amazonConfig;
    }

    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $captureNow = ($this->amazonConfig->getPaymentAction() == PaymentAction::AUTHORIZE_AND_CAPTURE);
        $response = $this->adapter->authorize($data, $captureNow);
        return $response;
    }
}
