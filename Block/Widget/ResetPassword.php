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

namespace Amazon\Pay\Block\Widget;

use Amazon\Pay\Model\AmazonConfig;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Url;
use Magento\Customer\Model\Session;
use Amazon\Pay\Api\CustomerLinkRepositoryInterface;

/**
 * @api
 */
class ResetPassword extends Template
{

    private $urlModel;

    private $session;

    private $customerLink;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    public function __construct(
        Context $context,
        Url $urlModel,
        Session $session,
        CustomerLinkRepositoryInterface $customerLink,
        AmazonConfig $amazonConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlModel = $urlModel;
        $this->session = $session;
        $this->customerLink = $customerLink;
        $this->amazonConfig = $amazonConfig;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('widget/resetpassword.phtml');
        }
        return $this;
    }

    public function getLink()
    {
        $url = $this->urlModel->getLogoutUrl();

        return $url;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->amazonConfig->isLwaEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
