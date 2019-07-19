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
namespace Amazon\Core\Model\Config\Backend;

/**
 * Backend model for processing encrypted private key settings
 *
 * Class Privatekey
 */
class Privatekey extends \Magento\Framework\App\Config\Value
{
    const PLACEHOLDER = '[encrypted]';

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Privatekey constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->encryptor = $encryptor;
    }

    /**
     * Check if config data value was changed
     *
     * @return bool
     */
    public function isValueChanged()
    {
        return $this->getValue() != self::PLACEHOLDER;
    }

    /**
     * Encrypt private key
     *
     * @return $this|\Magento\Framework\Model\AbstractModel
     */
    public function beforeSave()
    {
        $currentValue = $this->getValue();
        if ($this->isValueChanged() && $currentValue) {
            $replaceValue = $this->encryptor->encrypt($currentValue);
            $this->setValue($replaceValue);
        }

        return $this;
    }

    /**
     * Save object data
     *
     * @return $this
     * @throws \Exception
     */
    public function save()
    {
        if ($this->isValueChanged()) {
            parent::save();
        }
        return $this;
    }

    /**
     * Set placeholder if value set
     *
     * @return $this|\Magento\Framework\Model\AbstractModel
     */
    protected function _afterLoad()
    {
        if ($this->getValue()) {
            $this->setValue(self::PLACEHOLDER);
        }

        return $this;
    }
}
