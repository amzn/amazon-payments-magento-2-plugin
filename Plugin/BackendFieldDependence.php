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
namespace Amazon\Pay\Plugin;

class BackendFieldDependence
{
    /**
     * @var \Magento\Framework\App\RequestInterface $request
     */
    private $request;

    /**
     * @var \Magento\Paypal\Helper\Backend $backendHelper
     */
    private $backendHelper;

    /**
     * BackendFieldDependence constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Paypal\Helper\Backend $backendHelper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Paypal\Helper\Backend $backendHelper
    ) {
        $this->backendHelper = $backendHelper;
        $this->request = $request;
    }

    /**
     * Update field name dependence when Merchant Country is changed

     * @param \Magento\Backend\Block\Widget\Form\Element\Dependence $subject
     * @param string $fieldId - element ID in DOM
     * @param string $fieldName - element name in their fieldset/form namespace
     * @return array
     */
    public function beforeAddFieldMap($subject, $fieldId, $fieldName)
    {
        if ($this->isAmazonField($fieldName)) {
            $code = $this->getCode();
            $fieldId = str_replace('_us_', "_{$code}_", $fieldId);
            $fieldName = str_replace('[us]', "[$code]", $fieldName);
        }
        return [$fieldId, $fieldName];
    }

    /**
     * Update field name dependence when Merchant Country is changed
     *
     * @param \Magento\Backend\Block\Widget\Form\Element\Dependence $subject
     * @param string $fieldName
     * @param string $fieldNameFrom
     * @param \Magento\Config\Model\Config\Structure\Element\Dependency\Field|string $refField
     * @return array
     */
    public function beforeAddFieldDependence(
        \Magento\Backend\Block\Widget\Form\Element\Dependence $subject,
        $fieldName,
        $fieldNameFrom,
        $refField
    ) {
        if ($this->isAmazonField($fieldNameFrom)) {
            $code = $this->getCode();
            $fieldNameFrom = str_replace('[us]', "[$code]", $fieldNameFrom);
        }
        return [$fieldName, $fieldNameFrom, $refField];
    }

    /**
     * Is Amazon config field on Payment section with non-US country?
     *
     * @param string $fieldName
     * @return bool
     */
    private function isAmazonField($fieldName)
    {
        return $this->request->getParam('section') == 'payment'
            && strpos($fieldName, 'amazon') !== false;
    }

    /**
     * Get merchant country code
     *
     * @return string
     */
    private function getCode()
    {
        $code = strtolower($this->backendHelper->getConfigurationCountryCode());
        return in_array($code, ['us','ca','au','gb','jp','fr','it','es','hk','nz','de']) ? $code : 'other';
    }
}
