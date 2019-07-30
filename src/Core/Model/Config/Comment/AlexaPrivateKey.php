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
namespace Amazon\Core\Model\Config\Comment;

use Magento\Framework\Model\Context;
use Magento\Config\Model\Config\CommentInterface;
use Amazon\Core\Helper\Data as AmazonCoreHelper;
use Amazon\Core\Model\Alexa;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\UrlInterface;

class AlexaPrivateKey implements CommentInterface
{
    /**
     * @var AmazonCoreHelper
     */
    protected $amazonCoreHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Alexa
     */
    protected $alexaModel;

    /**
     * AlexaComment constructor.
     * @param Context $context
     * @param AmazonCoreHelper $amazonCoreHelper
     * @param StoreManagerInterface $storeManager
     * @param Alexa $alexaModel
     */
    public function __construct(
        AmazonCoreHelper $amazonCoreHelper,
        StoreManagerInterface $storeManager,
        Alexa $alexaModel,
        UrlInterface $urlBuilder
    ) {
        $this->amazonCoreHelper = $amazonCoreHelper;
        $this->storeManager     = $storeManager;
        $this->alexaModel       = $alexaModel;
        $this->urlBuilder       = $urlBuilder;
    }

    /**
     * Dynamic comment text for Alexa Public Key ID
     *
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        $pubkey   = $this->amazonCoreHelper->getAlexaPublicKey();
        $privkey  = $this->amazonCoreHelper->getAlexaPrivateKey();

        $generateUrl = $this->urlBuilder->getUrl('amazon/alexa/generatekeys');
        $downloadUrl = $this->urlBuilder->getUrl('amazon/alexa/download');

        if (!$privkey) {
            return '<a href="' . $generateUrl . '">' . __('Generate a new public/private key pair for Amazon Pay') . '</a>';
        }
        else if ($pubkey) {
            return '<a href="' . $downloadUrl . '">' . __('Download Public Key') . '</a>';
        }
    }
}
