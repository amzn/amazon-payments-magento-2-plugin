<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
namespace Amazon\PayV2\Model\Config\Form;

/**
 * Frontend model to obscure decrypted textarea
 *
 * Class Privatekey
 */
class Privatekey extends \Magento\Config\Block\System\Config\Form\Field
{
    const OBSCURED = '******';

    /**
     * Retrieve element HTML markup and add OBSCURED textarea value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($element->getValue()) {
            $element->setValue(self::OBSCURED);
        }
        return $element->getElementHtml();
    }
}
