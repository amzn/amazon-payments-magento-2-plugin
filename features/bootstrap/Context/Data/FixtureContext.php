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
namespace Context\Data;

use Behat\Behat\Context\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class FixtureContext implements Context
{
    protected static $fixtures = [];

    public static function trackFixture($entity, $repository = null)
    {
        self::$fixtures[] = [
            'entity'     => $entity,
            'repository' => $repository
        ];
    }

    /**
     * @AfterScenario
     */
    public function deleteFixtures()
    {
        if (count(self::$fixtures)) {
            foreach (self::$fixtures as $fixture) {
                try {
                    if  (null !== $fixture['repository']) {
                        $fixture['repository']->delete($fixture['entity']);
                    } else {
                        $fixture['entity']->delete();
                    }
                } catch (NoSuchEntityException $e) {
                    //should have been deleted already sometimes items are tracked twice
                }
            }
        }

        self::$fixtures = [];
    }
}