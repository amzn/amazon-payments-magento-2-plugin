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
namespace Amazon\Core\Model;

use Amazon\Core\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;

class EnvironmentChecker
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var Data
     */
    private $coreHelper;

    /**
     * @var State
     */
    private $state;

    /**
     * EnvironmentChecker constructor.
     *
     * @param ScopeConfigInterface $config
     * @param Data                 $coreHelper
     * @param State                $state
     */
    public function __construct(ScopeConfigInterface $config, Data $coreHelper, State $state)
    {
        $this->config     = $config;
        $this->coreHelper = $coreHelper;
        $this->state      = $state;
    }

    /**
     * Check if behat is running
     *
     * @return bool
     */
    public function isTestMode()
    {
        return (
            '1' === $this->config->getValue('is_behat_running')
            && $this->coreHelper->isSandboxEnabled()
            && State::MODE_PRODUCTION !== $this->state->getMode()
        );
    }
}
