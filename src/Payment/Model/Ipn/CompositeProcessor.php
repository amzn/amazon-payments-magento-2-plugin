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
namespace Amazon\Payment\Model\Ipn;

use Amazon\Payment\Api\Ipn\CompositeProcessorInterface;
use Amazon\Payment\Api\Ipn\ProcessorInterface;

class CompositeProcessor implements CompositeProcessorInterface
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    public function __construct(array $processors)
    {
        $this->processors = $processors;
    }

    /**
     * {@inheritDoc}
     */
    public function process(array $ipnData)
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports($ipnData)) {
                return $processor->process($ipnData);
            }
        }
    }
}
