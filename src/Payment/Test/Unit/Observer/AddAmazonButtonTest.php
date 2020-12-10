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
namespace Amazon\Payment\Test\Unit\Observer;

use Amazon\Payment\Block\Minicart\Button;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Amazon\Payment\Observer\AddAmazonButton;

/**
 * Class AddAmazonButtonTest
 *
 * @see \Amazon\Payment\Observer\AddAmazonButton
 *
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class AddAmazonButtonTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute()
    {

        $objectManager = new ObjectManager($this);
        $data = $objectManager->getObject(\Amazon\Core\Helper\Data::class);
        $shortcutFactory = $objectManager->getObject(\Amazon\Payment\Helper\Shortcut\Factory::class);
        $addAmazonButton = new AddAmazonButton($data, $shortcutFactory);

        /** @var Observer|\PHPUnit_Framework_MockObject_MockObject $observerMock */
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Event|\PHPUnit_Framework_MockObject_MockObject $eventMock */
        $eventMock = $this->getMockBuilder(Event::class)
            ->setMethods(['getContainer'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ShortcutButtons|\PHPUnit_Framework_MockObject_MockObject $shortcutButtonsMock */
        $shortcutButtonsMock = $this->getMockBuilder(ShortcutButtons::class)
            ->disableOriginalConstructor()
            ->getMock();

        $observerMock->expects(self::once())
            ->method('getEvent')
            ->willReturn($eventMock);

        $eventMock->expects(self::once())
            ->method('getContainer')
            ->willReturn($shortcutButtonsMock);

        $addAmazonButton->execute($observerMock);
    }
}
