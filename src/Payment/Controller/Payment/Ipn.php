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
namespace Amazon\Payment\Controller\Payment;

use Amazon\Core\Exception\AmazonWebapiException;
use Amazon\Core\Helper\Data;
use Amazon\Core\Model\Config\Source\UpdateMechanism;
use Amazon\Core\Logger\ExceptionLogger;
use Amazon\Payment\Api\Ipn\CompositeProcessorInterface;
use Amazon\Payment\Ipn\IpnHandlerFactoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\ObjectManager;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class Ipn extends Action
{
    /**
     * @var IpnHandlerFactoryInterface
     */
    private $ipnHandlerFactory;

    /**
     * @var CompositeProcessorInterface
     */
    private $compositeProcessor;

    /**
     * @var Data
     */
    private $coreHelper;

    /**
     * @var ExceptionLogger
     */
    private $exceptionLogger;

    public function __construct(
        Context $context,
        IpnHandlerFactoryInterface $ipnHandlerFactory,
        CompositeProcessorInterface $compositeProcessor,
        Data $coreHelper,
        ExceptionLogger $exceptionLogger = null
    ) {
        parent::__construct($context);
        $this->ipnHandlerFactory  = $ipnHandlerFactory;
        $this->compositeProcessor = $compositeProcessor;
        $this->coreHelper         = $coreHelper;
        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
    }

    public function execute()
    {
        try {
            if (UpdateMechanism::IPN !== $this->coreHelper->getUpdateMechanism()) {
                throw new NotFoundException(__('IPN not enabled.'));
            }

            $headers = $this->_request->getHeaders()->toArray();
            $body = $this->_request->getContent();

            $ipnHandler = $this->ipnHandlerFactory->create($headers, $body);
            $ipnData = $ipnHandler->toArray();
            $this->compositeProcessor->process($ipnData);
        } catch (\Exception $e) {
            $this->exceptionLogger->logException($e);
            throw $e;
        }
    }
}
