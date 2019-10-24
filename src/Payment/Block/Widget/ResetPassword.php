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
namespace Amazon\Payment\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Session;
use Amazon\Login\Api\CustomerLinkRepositoryInterface;

/**
 * @api
 */
class ResetPassword extends Template
{
    /**
     * @var UrlInterface
     */
    private $urlModel;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CustomerLinkRepositoryInterface
     */
    private $customerLink;

    /**
     * @param Context                         $context
     * @param UrlFactory                      $urlFactory
     * @param Session                         $session
     * @param CustomerLinkRepositoryInterface $customerLink
     * @param array                           $data
     */
    public function __construct(
        Context $context,
        UrlFactory $urlFactory,
        Session $session,
        CustomerLinkRepositoryInterface $customerLink,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlModel = $urlFactory->create();
        $this->session = $session;
        $this->customerLink = $customerLink;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('widget/resetpassword.phtml');
        }
        return $this;
    }

    /*
     * Check to show link for customer
     *
     * @return bool
     */
    public function displayAmazonInfo()
    {
        $id = $this->session->getCustomer()->getId();

        $amazon = $this->customerLink->get($id);

        if ($amazon && $amazon->getAmazonId()) {
            return true;
        }

        return false;
    }

    /* 
     * Get link for block
     *
     * @return string
     */
    public function getLink()
    {
        $url = $this->urlModel->getUrl('customer/account/forgotpassword');

        return $url;
    }
}
