<?php

namespace Amazon\Pay\Api\Spc\Response;

interface NameValueInterface
{
    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Get value
     *
     * @return string
     */
    public function getValue();

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name);

    /**
     * Set value
     *
     * @param string $value
     * @return $this
     */
    public function setValue(string $value);
}
