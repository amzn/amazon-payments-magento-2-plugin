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

namespace Amazon\Payment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Amazon\Core\Client\ClientFactoryInterface;
use Amazon\Payment\Gateway\Helper\SubjectReader;
use Amazon\Core\Helper\Data;
use Amazon\Payment\Model\Adapter\AmazonPaymentAdapter;

/**
 * Class AbstractClient
 * Base class for gateway client classes
 */
abstract class AbstractClient implements ClientInterface
{
    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ClientFactoryInterface
     */
    protected $clientFactory;

    /**
     * @var Data
     */
    protected $coreHelper;

    /**
     * @var AmazonPaymentAdapter
     */
    protected $adapter;

    /**
     * AbstractClient constructor.
     * @param Logger $logger
     * @param ClientFactoryInterface $clientFactory
     * @param SubjectReader $subjectReader
     * @param Data $coreHelper
     * @param AmazonPaymentAdapter $adapter
     */
    public function __construct(
        Logger $logger,
        ClientFactoryInterface $clientFactory,
        SubjectReader $subjectReader,
        Data $coreHelper,
        AmazonPaymentAdapter $adapter
    ) {
        $this->subjectReader = $subjectReader;
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
        $this->coreHelper = $coreHelper;
        $this->adapter = $adapter;
    }

    /**
     * @inheritdoc
     */
    public function placeRequest(TransferInterface $transferObject)
    {

        $data = $transferObject->getBody();

        $log = [
            'request' => $transferObject->getBody(),
            'client' => static::class
        ];

        $response = [];

        try {
            $response = $this->process($data);
        } catch (\Exception $e) {
            $message = $e->getMessage() ? $e->getMessage() : "Something went wrong during Gateway request.";
            $log['error'] = $message;
            $this->logger->debug($log);
        }

        return $response;
    }

    /**
     * Process http request
     *
     * @param array $data
     */
    abstract protected function process(array $data);
}
