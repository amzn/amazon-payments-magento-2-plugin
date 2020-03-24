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
namespace Amazon\PayV2\Controller\Checkout;

/**
 * Class CreateSession
 */
class CreateSession extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Amazon\PayV2\CustomerData\CheckoutSession
     */
    private $amazonCheckoutSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Amazon\PayV2\CustomerData\CheckoutSession $amazonCheckoutSession
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->amazonCheckoutSession = $amazonCheckoutSession;
    }

    /*
     * @inheritdoc
     */
    public function execute()
    {
        $checkoutSessionId = $this->amazonCheckoutSession->createCheckoutSessionId();
        $data = [
            'checkoutSessionId' => $checkoutSessionId,
        ];
        return $this->resultJsonFactory->create()->setData($data);
    }
}
