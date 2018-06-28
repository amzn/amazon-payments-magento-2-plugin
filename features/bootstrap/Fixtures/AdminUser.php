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
use Magento\Framework\Math\Random;
use Magento\User\Model\UserFactory;

class AdminUser extends BaseFixture
{
    /**
     * @var UserFactory
     */
    private $factory;

    /**
     * @var Random
     */
    private $random;

    public function __construct()
    {
        parent::__construct();
        $this->factory = $this->getMagentoObject(UserFactory::class);
        $this->random  = $this->getMagentoObject(Random::class);
    }

    public function generate()
    {
        $data = [
            'firstname' => 'John',
            'lastname'  => 'Doe',
            'username'  => $this->getDefaultUsername(),
            'email'     => 'admin-' .time() . '@example.com',
            'password'  => $this->getDefaultPassword(),
            'role_id'   => 1
        ];

        $user = $this->factory->create()->setData($data)->save();

        FixtureContext::trackFixture($user);

        return $user;
    }

    public function getDefaultUsername()
    {
        static $defaultUsername = null;

        if (null === $defaultUsername) {
            $defaultUsername = $this->random->getRandomString(20, Random::CHARS_LOWERS);
        }

        return $defaultUsername;
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
}
