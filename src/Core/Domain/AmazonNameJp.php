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

class AmazonNameJp extends AmazonName
{

    /**
     * AmazonName constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $name      = mb_convert_kana($name, 's', 'utf-8');
        $nameParts       = explode(' ', trim($name), 2);
        $this->firstName = isset($nameParts[1]) ? $nameParts[1] : '.';
        $this->lastName  = $nameParts[0];
    }
}
