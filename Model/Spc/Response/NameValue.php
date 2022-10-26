<?php

namespace Amazon\Pay\Model\Spc\Response;

use Amazon\Pay\Api\Spc\Response\NameValueInterface;
use Magento\Framework\DataObject;

class NameValue extends DataObject implements NameValueInterface
{
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->_getData('value');
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name)
    {
        return $this->setData('name', $name);
    }

    /**
     * @inheritDoc
     */
    public function setValue(string $value)
    {
        return $this->setData('value', $value);
    }
}
