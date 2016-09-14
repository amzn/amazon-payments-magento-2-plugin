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
namespace Page\Element;

trait ElementHelper
{
    /**
     * @param string $selector
     * @param string|array $locator
     * @return \Behat\Mink\Element\NodeElement|null
     * @see \Behat\Mink\Element\ElementInterface::find()
     */
    abstract public function find($selector, $locator);

    /**
     * @param string $cssQuery
     * @param bool $strict
     * @return \Behat\Mink\Element\NodeElement
     * @throws \Exception
     */
    protected function findElement($cssQuery, $strict = true)
    {
        $element = $this->find('css', $cssQuery);

        if ($strict && $element === null) {
            throw new \Exception('No element found with CSS query: ' . $cssQuery);
        }

        return $element;
    }
}
