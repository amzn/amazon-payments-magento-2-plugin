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
namespace Amazon\Alexa\Model\Config\Comment;

class AlexaPrivateKey implements \Magento\Config\Model\Config\CommentInterface
{
    /**
     * @var \Amazon\Alexa\Model\AlexaConfig
     */
    protected $alexaConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Amazon\Alexa\Model\Alexa
     */
    protected $alexaModel;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $urlBuilder;

    /**
     * AlexaPrivateKey constructor.
     * @param \Amazon\Alexa\Model\AlexaConfig $alexaConfig,
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Amazon\Alexa\Model\Alexa $alexaModel
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     */
    public function __construct(
        \Amazon\Alexa\Model\AlexaConfig $alexaConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amazon\Alexa\Model\Alexa $alexaModel,
        \Magento\Backend\Model\UrlInterface $urlBuilder
    ) {
        $this->alexaConfig      = $alexaConfig;
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
        $pubkey  = $this->alexaConfig->getAlexaPublicKey();
        $privkey = $this->alexaConfig->getAlexaPrivateKey();

        $generateUrl = $this->urlBuilder->getUrl('amazon_alexa/alexa/generatekeys');
        $downloadUrl = $this->urlBuilder->getUrl('amazon_alexa/alexa/download');

        if (!$privkey) {
            return '<a href="' . $generateUrl . '">' . __('Generate a new public/private key pair for Amazon Pay') . '</a>';
        } elseif ($pubkey) {
            return '<a href="' . $downloadUrl . '">' . __('Download Public Key') . '</a>';
        }
    }
}
