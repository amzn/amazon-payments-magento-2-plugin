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

use Magento\Config\Model\Config\CommentInterface;
use Amazon\Core\Helper\Data as AmazonCoreHelper;
use Amazon\Core\Model\Alexa;
use Amazon\Core\Logger\Handler\Alexa as AlexaLoggerHandler;
use Magento\Store\Model\StoreManagerInterface;

class AlexaPublicKeyId implements CommentInterface
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
     *
     * @param AmazonCoreHelper $amazonCoreHelper
     * @param StoreManagerInterface $storeManager
     * @param Alexa $alexaModel
     */
    public function __construct(
        AmazonCoreHelper $amazonCoreHelper,
        StoreManagerInterface $storeManager,
        Alexa $alexaModel
    ) {
        $this->amazonCoreHelper = $amazonCoreHelper;
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
        $pubkeyid = $this->amazonCoreHelper->getAlexaPublicKeyId();
        $pubkey   = $this->amazonCoreHelper->getAlexaPublicKey();
        $privkey  = $this->amazonCoreHelper->getAlexaPrivateKey();

        $comment = '';

        if (!$pubkeyid) {
            if (!$pubkey) {
                $this->alexaModel->generateKeys();
                $pubkey  = $this->amazonCoreHelper->getAlexaPublicKey();
                $privkey = $this->amazonCoreHelper->getAlexaPrivateKey();
            }
            if ($privkey) {
                $merchantId = $this->amazonCoreHelper->getMerchantId();
                $subject = rawurlencode('Request for Amazon Pay Public Key ID for ' . $merchantId);
                $body = rawurlencode("Merchant ID: $merchantId\n\nPublic Key:\n\n$pubkey");
                $comment = __('Please <a href="%1">contact</a> Amazon Pay to receive the Public Key ID.',
                    'mailto:Amazon-pay-delivery-notifications@amazon.com?subject=' . $subject . '&body=' . $body);
            }
        }
        $comment .= '<br/><br/>' . __('Alexa Delivery Notifications logs will be saved at %1',
                AlexaLoggerHandler::FILENAME);
        return $comment;
    }
}
