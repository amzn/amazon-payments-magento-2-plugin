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

class PrivateKey implements \Magento\Config\Model\Config\CommentInterface
{
    /**
     * @var \Amazon\PayV2\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $urlBuilder;

    /**
     * PayV2PrivateKey constructor.
     * @param \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     */
    public function __construct(
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\UrlInterface $urlBuilder
    ) {
        $this->amazonConfig      = $amazonConfig;
        $this->storeManager     = $storeManager;
        $this->urlBuilder       = $urlBuilder;
    }

    /**
     * Dynamic comment text for PayV2 Public Key ID
     *
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        $pubkey  = $this->amazonConfig->getPublicKey();
        $privkey = $this->amazonConfig->getPrivateKey();

        $generateUrl = $this->urlBuilder->getUrl('amazon_payv2/payv2/generatekeys');
        $publicKeyUrl = $this->urlBuilder->getUrl('amazon_payv2/payv2/download/key/public');
        $privateKeyUrl = $this->urlBuilder->getUrl('amazon_payv2/payv2/download/key/private');

        if (!$privkey) {
            return '<a href="' . $generateUrl . '">' . __('Generate a new public/private key pair for Amazon Pay') . '</a>';
        } elseif ($pubkey) {
            $commentText = '<a href="' . $publicKeyUrl . '">' . __('Download Public Key') . '</a>';
            $commentText .= '<br><a href="' . $privateKeyUrl . '">' . __('Download Private Key') . '</a>';
            return $commentText;
        }
    }
}
