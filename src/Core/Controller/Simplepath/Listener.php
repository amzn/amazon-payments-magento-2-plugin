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

namespace Amazon\Core\Controller\Simplepath;

use Amazon\Core\Logger\ExceptionLogger;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class Listener
 * Retrieves entered keys from Amazon Pay popup
 */
class Listener extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

    // @var \Magento\Framework\Controller\Result\JsonFactory
    private $jsonResultFactory;

    // @var \Amazon\Core\Model\Config\SimplePath
    private $simplepath;

    // @var \Amazon\Core\Logger\ExceptionLogger
    private $exceptionLogger;

    /**
     * Listener constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Amazon\Core\Model\Config\SimplePath $simplepath
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Amazon\Core\Logger\ExceptionLogger $exceptionLogger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Amazon\Core\Model\Config\SimplePath $simplepath,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        ExceptionLogger $exceptionLogger = null
    ) {
        $this->simplepath = $simplepath;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->scopeConfig = $scopeConfig;
        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
        parent::__construct($context);
    }

    /**
     * Parse POST request from Amazon and import keys
     */
    public function execute()
    {
        try {
            $host = parse_url($this->getRequest()->getHeader('Origin'))['host'];
            if (in_array($host, $this->simplepath->getListenerOrigins())) {
                $this->getResponse()->setHeader('Access-Control-Allow-Origin', 'https://' . $host);
            }
            $this->getResponse()->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $this->getResponse()->setHeader('Access-Control-Allow-Headers', 'Content-Type, X-CSRF-Token');
            $this->getResponse()->setHeader('Vary', 'Origin');

            $payload = $this->_request->getParam('payload');

            $result = $this->jsonResultFactory->create();

            $return = ['result' => 'error', 'message' => 'Empty payload'];

            try {
                if (strpos($payload, 'encryptedKey') === false) {
                    $return = ['result' => 'error', 'message' => 'Invalid payload: ' . $payload];
                } elseif ($payload) {
                    $json = $this->simplepath->decryptPayload($payload, false);

                    if ($json) {
                        $return = ['result' => 'success'];
                    }
                } else {
                    $return = ['result' => 'error', 'message' => 'payload parameter not found.'];
                }
            } catch (\Exception $e) {
                $return = ['result' => 'error', 'message' => $e->getMessage()];
            }

            if ($this->_request->isPost() && (empty($return['result']) || $return['result'] == 'error')) {
                $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);
            }

            $result->setData($return);

            return $result;
        } catch(\Exception $e) {
            $this->exceptionLogger->logException($e);
            throw $e;
        }
    }

    /**
     * Overridden to allow POST without form key
     *
     * @return bool
     */
    public function _processUrlKeys()
    {
        $_isValidFormKey = true;
        $_isValidSecretKey = true;
        $_keyErrorMsg = '';
        if ($this->_auth->isLoggedIn()) {
            if ($this->_backendUrl->useSecretKey()) {
                $_isValidSecretKey = $this->_validateSecretKey();
                $_keyErrorMsg = __('You entered an invalid Secret Key. Please refresh the page.');
            }
        }
        if (!$_isValidFormKey || !$_isValidSecretKey) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
            if ($this->getRequest()->getQuery('isAjax', false) || $this->getRequest()->getQuery('ajax', false)) {
                $this->getResponse()->representJson(
                    $this->_objectManager->get(
                        \Magento\Framework\Json\Helper\Data::class
                    )->jsonEncode(
                        ['error' => true, 'message' => $_keyErrorMsg]
                    )
                );
            } else {
                $this->_redirect($this->_backendUrl->getStartupPageUrl());
            }
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Disable Magento's CSRF validation.
     *
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
