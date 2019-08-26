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

use Amazon\Alexa\Logger\Handler\Alexa as AlexaLoggerHandler;

class AlexaPublicKeyId implements \Magento\Config\Model\Config\CommentInterface
{
    /**
     * @var \Amazon\Alexa\Model\AlexaConfig
     */
    private $alexaConfig;

    /**
     * @var \Amazon\Core\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amazon\Alexa\Model\Alexa
     */
    private $alexaModel;

    /**
     * AlexaComment constructor.
     *
     * @param \Amazon\Alexa\Model\AlexaConfig $alexaConfig
     * @param \Amazon\Core\Model\AmazonConfig $amazonConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Amazon\Alexa\Model\Alexa $alexaModel
     */
    public function __construct(
        \Amazon\Alexa\Model\AlexaConfig $alexaConfig,
        \Amazon\Core\Model\AmazonConfig $amazonConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amazon\Alexa\Model\Alexa $alexaModel
    ) {
        $this->alexaConfig      = $alexaConfig;
        $this->amazonConfig     = $amazonConfig;
        $this->storeManager     = $storeManager;
        $this->alexaModel       = $alexaModel;
    }

    /**
     * Dynamic comment text for Alexa key
     *
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        $pubkeyid = $this->alexaConfig->getAlexaPublicKeyId();
        $pubkey   = $this->alexaConfig->getAlexaPublicKey();
        $privkey  = $this->alexaConfig->getAlexaPrivateKey();

        $comment = '';

        if (!$pubkeyid && $privkey) {
            $merchantId = $this->amazonConfig->getMerchantId();
            $subject = rawurlencode('Request for Amazon Pay Public Key ID for ' . $merchantId);
            $body = rawurlencode("Merchant ID: $merchantId\n\nPublic Key:\n\n$pubkey");
            $comment = __(
                'Please <a href="%1">contact</a> Amazon Pay to receive the Public Key ID.',
                'mailto:Amazon-pay-delivery-notifications@amazon.com?subject=' . $subject . '&body=' . $body
            );
        }
        $comment .= '<br/><br/>' . __(
            'Alexa Delivery Notifications logs will be saved at .%1',
            AlexaLoggerHandler::FILENAME
        );
        return $comment;
    }
}
