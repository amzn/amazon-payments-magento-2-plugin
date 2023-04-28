<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 */

namespace Amazon\Pay\Helper;

class Key
{

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @var int
     */
    protected $_websiteId;

    /**
     * @var string
     */
    protected $_scope;

    /**
     * @var int
     */
    protected $_scopeId;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;

        // Find store ID and scope
        $this->_websiteId = $request->getParam('website', 0);
        $this->_storeId = $request->getParam('store', 0);
        $this->_scope = $request->getParam('scope');

        // Website scope
        if ($this->_websiteId) {
            $this->_scope = !$this->_scope ? 'websites' : $this->_scope;
        } else {
            $this->_websiteId = $storeManager->getWebsite()->getId();
        }

        // Store scope
        if ($this->_storeId) {
            $this->_websiteId = $this->storeManager->getStore($this->_storeId)->getWebsite()->getId();
            $this->_scope = !$this->_scope ? 'stores' : $this->_scope;
        } else {
            $this->_storeId = $storeManager->getWebsite($this->_websiteId)->getDefaultStore()->getId();
        }

        // Set scope ID
        switch ($this->_scope) {
            case 'websites':
                $this->_scopeId = $this->_websiteId;
                break;
            case 'stores':
                $this->_scopeId = $this->_storeId;
                break;
            default:
                $this->_scope = 'default';
                $this->_scopeId = 0;
                break;
        }
    }

    /**
     * Generate and save RSA keys
     *
     * @return array
     */
    public function generateKeys()
    {
        // Magento 2.4.4 switches to phpseclib3, use that if it exists
        if (class_exists(\phpseclib3\Crypt\RSA::class, true)) {
            $keypair = \phpseclib3\Crypt\RSA::createKey(2048);
            $keys = [
                "publickey" => $keypair->getPublicKey()->__toString(),
                "privatekey" => $keypair->__toString()
            ];
        } else {
            $rsa = new \phpseclib\Crypt\RSA();
            $keys = $rsa->createKey(2048);
        }

        return $keys;
    }

    /**
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->_websiteId;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->_scope;
    }

    /**
     * @return int
     */
    public function getScopeId()
    {
        return $this->_scopeId;
    }
}
