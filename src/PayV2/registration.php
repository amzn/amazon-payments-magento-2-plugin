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
use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();

$moduleVisibilityFlag = 'AMAZON_PAYV2_ENABLE';
$isModuleVisible = !empty($_ENV[$moduleVisibilityFlag]) || !empty($_SERVER[$moduleVisibilityFlag]) || file_exists(BP . '/var/.' . strtolower($moduleVisibilityFlag));

if ($isModuleVisible && $registrar->getPath(ComponentRegistrar::MODULE, 'Amazon_PayV2') === null) {
    ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Amazon_PayV2', __DIR__);
}
