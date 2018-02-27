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

use Amazon\Core\Helper\Data;
use Amazon\Core\Model\Config\Source\UpdateMechanism;
use Amazon\Payment\Api\Ipn\CompositeProcessorInterface;
use Amazon\Payment\Ipn\IpnHandlerFactoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;

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

    public function __construct(
        Context $context,
        IpnHandlerFactoryInterface $ipnHandlerFactory,
        CompositeProcessorInterface $compositeProcessor,
        Data $coreHelper
    ) {
        parent::__construct($context);
        $this->ipnHandlerFactory  = $ipnHandlerFactory;
        $this->compositeProcessor = $compositeProcessor;
        $this->coreHelper         = $coreHelper;
    }

    public function execute()
    {
        if (UpdateMechanism::IPN !== $this->coreHelper->getUpdateMechanism()) {
            throw new NotFoundException(__('IPN not enabled.'));
        }

        $headers = $this->_request->getHeaders()->toArray();
        $body    = $this->_request->getContent();

        $ipnHandler = $this->ipnHandlerFactory->create($headers, $body);
        $ipnData    = $ipnHandler->toArray();
        $this->compositeProcessor->process($ipnData);
    }
}
