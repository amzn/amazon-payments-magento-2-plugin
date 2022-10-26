<?php

namespace Amazon\Pay\Api\Spc\Response;

interface NameValueInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getValue();

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name);

    /**
     * @param string $value
     * @return $this
     */
    public function setValue(string $value);
}
