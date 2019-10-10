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
namespace Amazon\PayV2\Model\Config\Comment;

class PublicKeyId implements \Magento\Config\Model\Config\CommentInterface
{
    /**
     * @var \Amazon\PayV2\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * PayV2 Comment constructor.
     *
     * @param \Amazon\PayV2\Model\AmazonConfig $amazonConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Amazon\PayV2\Model\PayV2 $PayV2Model
     */
    public function __construct(
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->amazonConfig     = $amazonConfig;
        $this->storeManager     = $storeManager;
    }

    /**
     * Dynamic comment text for PayV2 key
     *
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        $pubkeyid = $this->amazonConfig->getPublicKeyId();
        $pubkey   = $this->amazonConfig->getPublicKey();
        $privkey  = $this->amazonConfig->getPrivateKey();

        $comment = '';

/*  Hiding this for now.  To be reintroduced in a later version
        if (!$pubkeyid && $privkey) {
            $merchantId = $this->amazonConfig->getMerchantId();
            $subject = rawurlencode('Request for Amazon Pay Public Key ID for ' . $merchantId);
            $body = rawurlencode("Merchant ID: $merchantId\n\nPublic Key:\n\n$pubkey");
            $comment = __(
                'Please <a href="%1">contact</a> Amazon Pay to receive the Public Key ID.',
                'mailto:Amazon-pay-delivery-notifications@amazon.com?subject=' . $subject . '&body=' . $body
            );
        }
*/
        return $comment;
    }
}
