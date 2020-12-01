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
namespace Amazon\Core\Domain;

use Amazon\Core\Api\Data\AmazonCustomerInterface;
use Amazon\Core\Api\Data\AmazonNameInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Escaper;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class AmazonCustomerFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * @var AmazonNameFactory
     */
    private $amazonNameFactory;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * AmazonCustomerFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param AmazonNameFactory $amazonNameFactory
     * @param Escaper $escaper
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        AmazonNameFactory $amazonNameFactory,
        Escaper $escaper
    ) {
        $this->objectManager  = $objectManager;
        $this->amazonNameFactory = $amazonNameFactory;
        $this->escaper = $escaper;
    }

    /**
     * @param array $data
     * @return AmazonCustomer
     */
    public function create(array $data = [])
    {
        $amazonName = $this->amazonNameFactory
            ->create(['name' => $this->escaper->escapeHtml($data['name']), 'country' => $this->escaper->escapeHtml($data['country'])]);
        $data[AmazonNameInterface::FIRST_NAME] = $amazonName->getFirstName();
        $data[AmazonNameInterface::LAST_NAME] = $amazonName->getLastName();
        return $this->objectManager->create(AmazonCustomer::class, ['data' => $data]);
    }
}
