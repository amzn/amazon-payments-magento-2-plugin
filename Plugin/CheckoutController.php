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
namespace Amazon\Pay\Plugin;

use Magento\Checkout\Controller\Index\Index;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\UrlInterface;

class CheckoutController
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * CheckoutController constructor
     *
     * @param Session $session
     * @param UrlInterface $url
     */
    public function __construct(Session $session, UrlInterface $url)
    {
        $this->session = $session;
        $this->url     = $url;
    }

    /**
     * Set redirect URL in customer session
     *
     * @param Index $index
     * @param ResultInterface $result
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Index $index, ResultInterface $result)
    {
        $this->session->setAfterAmazonAuthUrl($this->url->getUrl('checkout'));

        return $result;
    }
}
