<?php

namespace Amazon\Pay\Model\Resolver\StoreConfig;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Amazon\Pay\Model\AmazonConfig;

class IsEnabled implements ResolverInterface
{
    private AmazonConfig $amazonConfig;

    public function __construct(
        AmazonConfig $amazonConfig
    ) {
        $this->amazonConfig = $amazonConfig;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        return $this->amazonConfig->isEnabled();
    }
}