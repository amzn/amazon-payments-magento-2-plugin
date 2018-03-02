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
namespace Amazon\Payment\Domain;

use Amazon\Core\Exception\AmazonServiceUnavailableException;
use Amazon\Payment\Domain\Details\AmazonCaptureDetails;
use Amazon\Payment\Domain\Details\AmazonCaptureDetailsFactory;
use AmazonPay\ResponseInterface;

abstract class AbstractAmazonCaptureResponse
{
    /**
     * @var AmazonCaptureDetails
     */
    private $details;

    /**
     * AbstractAmazonCaptureResponse constructor.
     *
     * @param ResponseInterface           $response
     * @param AmazonCaptureDetailsFactory $amazonCaptureDetailsFactory
     */
    public function __construct(
        ResponseInterface $response,
        AmazonCaptureDetailsFactory $amazonCaptureDetailsFactory
    ) {
        $data = $response->toArray();

        if (200 != $data['ResponseStatus']) {
            throw new AmazonServiceUnavailableException();
        }

        $details = $data[$this->getResultKey()]['CaptureDetails'];

        $this->details = $amazonCaptureDetailsFactory->create([
            'details' => $details
        ]);
    }

    /**
     * @return AmazonCaptureDetails
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Get result key
     *
     * @return string
     */
    abstract protected function getResultKey();
}
