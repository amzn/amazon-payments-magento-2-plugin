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
namespace Fixtures;

use Bex\Behat\Magento2InitExtension\Fixtures\BaseFixture;
use Context\Data\FixtureContext;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;

class Customer extends BaseFixture
{
    private $defaults
        = [
            CustomerInterface::FIRSTNAME => 'John',
            CustomerInterface::LASTNAME  => 'Doe',
            CustomerInterface::EMAIL     => 'customer@example.com'
        ];

    /**
     * @var CustomerRepositoryInterface
     */
    private $repository;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var Random
     */
    private $random;

    public function __construct()
    {
        parent::__construct();
        $this->repository = $this->getMagentoObject(CustomerRepositoryInterface::class);
        $this->encryptor  = $this->getMagentoObject(EncryptorInterface::class);
        $this->random     = $this->getMagentoObject(Random::class);
    }

    public function create(array $data)
    {
        $data         = array_merge($this->defaults, $data);
        $password     = (isset($data['password'])) ? $data['password'] : $this->getDefaultPassword();
        $passwordHash = $this->encryptor->getHash($password, true);

        $customerData = $this->createMagentoObject(CustomerInterface::class, ['data' => $data]);
        $customer     = $this->repository->save($customerData, $passwordHash);

        FixtureContext::trackFixture($customer, $this->repository);

        return $customer;
    }

    public function get($email, $ignoreRegistry = false)
    {
        $repository = ($ignoreRegistry) ? $this->createRepository() : $this->repository;
        return $repository->get($email);
    }

    public function track($email)
    {
        try {
            $customer = $this->get($email, true);
            FixtureContext::trackFixture($customer, $this->repository);
        } catch (NoSuchEntityException $e) {
            //entity not created no need to track for deletion
        }
    }

    public function getDefaultPassword()
    {
        static $defaultPassword = null;

        if (null === $defaultPassword) {
            $defaultPassword
                = $this->random->getRandomString(7, Random::CHARS_LOWERS)
                . $this->random->getRandomString(7, Random::CHARS_UPPERS)
                . $this->random->getRandomString(6, Random::CHARS_DIGITS);
        }

        return $defaultPassword;
    }

    protected function createRepository()
    {
        return $this->createMagentoObject(CustomerRepositoryInterface::class);
    }
}