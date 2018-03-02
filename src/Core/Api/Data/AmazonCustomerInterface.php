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
namespace Amazon\Core\Api\Data;

/**
 * @api
 */
interface AmazonCustomerInterface
{
    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName();

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Get id
     *
     * @return string
     */
    public function getId();

    /**
     * Set full name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Set id
     *
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set country
     *
     * @param string $country
     * @return $this
     */
    public function setCountry($country);
}
