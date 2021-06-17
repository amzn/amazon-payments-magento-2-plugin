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

namespace Amazon\Pay\Controller\AutoKeyExchange;

use Amazon\Pay\Logger\ExceptionLogger;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ObjectManager;

class Listener extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @var \Amazon\Pay\Model\Config\AutoKeyExchange
     */
    private $autokeyexchange;

    /**
     * @var \Amazon\Pay\Logger\ExceptionLogger
     */
    private $exceptionLogger;

    /**
     * Listener constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Amazon\Pay\Model\Config\AutoKeyExchange $autokeyexchange
     * @param \Amazon\Pay\Logger\ExceptionLogger $exceptionLogger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Amazon\Pay\Model\Config\AutoKeyExchange $autokeyexchange,
        ExceptionLogger $exceptionLogger = null
    ) {
        $this->autokeyexchange = $autokeyexchange;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
        parent::__construct($context);
    }

    /**
     * Parse POST request from Amazon and import keys
     */
    public function execute()
    {
        try {
            $originHeader = $this->getRequest()->getHeader('Origin');
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            if (!empty($originHeader) && $host = parse_url($originHeader, PHP_URL_HOST)) {
                if (in_array($host, $this->autokeyexchange->getListenerOrigins())) {
                    $this->getResponse()->setHeader('Access-Control-Allow-Origin', 'https://' . $host);
                }
            }

            $this->getResponse()->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $this->getResponse()->setHeader('Access-Control-Allow-Headers', 'Content-Type, X-CSRF-Token');
            $this->getResponse()->setHeader('Vary', 'Origin');

            $result = $this->jsonResultFactory->create();
            $authToken = $this->_request->getParam('auth');
            if ($this->autokeyexchange->validateAuthToken($authToken)) {
                $payload = $this->_request->getParam('payload');
                if ($this->autokeyexchange->decryptPayload($payload, false)) {
                    $status = ['result' => 'success'];
                } else {
                    $status = ['result' => 'error', 'message' => 'payload parameter not found.'];
                    $result->setHttpResponseCode(400);
                }
            } else {
                $status = ['result' => 'error', 'message' => 'invalid auth token.'];
                $result->setHttpResponseCode(400);
            }

            $result->setData($status);

            return $result;
        } catch (\Exception $e) {
            $this->exceptionLogger->logException($e);
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
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
