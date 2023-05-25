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
namespace Amazon\Pay\Logger;

use Psr\Log\LoggerInterface;

class ExceptionLogger
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ExceptionLogger constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Add an exception log
     *
     * @param \Exception $e
     * @return void
     */
    public function logException(\Exception $e)
    {
        $message = (string) $e;
        $this->logger->error($message);
    }
}
