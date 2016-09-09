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

class AmazonCustomer
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var AmazonName
     */
    protected $name;

    /**
     * AmazonCustomer constructor.
     *
     * @param string $id
     * @param string $email
     * @param string $name
     */
    public function __construct($id, $email, $name)
    {
        $this->id    = $id;
        $this->email = $email;
        $this->name = new AmazonName($name);
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->name->getFirstName();
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->name->getLastName();
    }
}
