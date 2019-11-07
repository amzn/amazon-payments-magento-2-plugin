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
namespace Amazon\Payment\Test\Unit\Helper;

use \Amazon\Login\Domain\LayoutConfig;
use \Amazon\Core\Model\AmazonConfig;

/**
 * Class LayoutConfigTest
 *
 * @see \Amazon\Login\Domain\LayoutConfig
 */
class LayoutConfigTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->mockAmazonConfig = $this->getMockBuilder(AmazonConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider lwaLayoutDataProvider
     */
    public function testIsLwaLayoutDisabled($expected, $isLwaEnabled)
    {
        $this->mockAmazonConfig->method('isLwaEnabled')->willReturn($isLwaEnabled);
        $layoutConfig = new LayoutConfig($this->mockAmazonConfig);
        $this->assertEquals($expected, $layoutConfig->isLwaLayoutDisabled());
    }

    public function lwaLayoutDataProvider()
    {
        return [
            [true, false],
            [false, true]
        ];
    }
}
