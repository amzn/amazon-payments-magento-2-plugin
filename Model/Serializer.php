<?php

namespace Amazon\Pay\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Serializer
{
    /**
     * @var null|SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param SerializerInterface $serializer/
     */
    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @param $value
     * @return bool|string
     */
    public function serialize($value)
    {
        try {
            return $this->serializer->serialize($value);
        } catch (\Exception $e) {
            return '{}';
        }
    }

    /**
     * @param $value
     * @return array|bool|float|int|string|null
     */
    public function unserialize($value)
    {
        if (false === $value || null === $value || '' === $value) {
            return false;
        }

        try {
            return $this->serializer->unserialize($value);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }
    }
}
