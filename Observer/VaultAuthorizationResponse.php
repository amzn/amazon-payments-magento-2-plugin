<?php
/**
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
namespace Amazon\Pay\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class VaultAuthorizationResponse implements ObserverInterface
{

    /**
     * @var \Amazon\Pay\Model\Adapter\AmazonPayAdapter $amazonAdapter
     */
    private $amazonAdapter;

    public function __construct(
        \Amazon\Pay\Model\Adapter\AmazonPayAdapter $amazonAdapter
    ) {
        $this->amazonAdapter = $amazonAdapter;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $response = $observer->getResponse();
        $order = $observer->getOrder();
        $state = $response['statusDetails']['state'];
        $status = $response['status'] ?? '404';
        if (preg_match('/^2\d\d$/', $status) && in_array($state,['Captured','CaptureInitiated'])) {
            /*$this->amazonAdapter->updateChargePermission(
                $order->getStoreId(),
                $response['chargePermissionId'],
                ['merchantReferenceId' => $order->getIncrementId()]
            );*/  
        }


       
    }
}
