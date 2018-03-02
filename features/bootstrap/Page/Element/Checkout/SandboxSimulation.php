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
namespace Page\Element\Checkout;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class SandboxSimulation extends Element
{
    private $selector = '.amazon-sandbox-simulator';

    const SIMULATION_REJECTED = 'Authorization:Declined:AmazonRejected';
    const SIMILATION_TIMEOUT = 'Authorization:Declined:TransactionTimedOut';
    const SIMULATION_INVALID_PAYMENT = 'Authorization:Declined:InvalidPaymentMethod';
    const SIMULATION_CAPTURE_PENDING = 'Capture:Pending';
    const SIMULATION_CAPTURE_DECLINED = 'Capture:Declined:AmazonRejected';
    const SIMULATION_REFUND_DECLINED = 'Refund:Declined';
    const NO_SIMULATION = 'default';

    public function selectSimulation($simulation)
    {
        $this->find('css', '#amazon-sandbox-simulator-heading')->click();
        $this->find('css', 'input[value="' . $simulation . '"]')->click();
        $this->find('css', '#amazon-sandbox-simulator-heading')->click();
    }
}