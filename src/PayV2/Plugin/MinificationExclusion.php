<?php

namespace Amazon\PayV2\Plugin;

use Magento\Framework\View\Asset\Minification;

/**
 * Class MinificationExclusion
 * @package Amazon\PayV2\Plugin
 */
class MinificationExclusion
{
    /**
     * @param Minification $subject
     * @param array $result
     * @param $contentType
     * @return array
     */
    public function afterGetExcludes(Minification $subject, array $result, $contentType)
    {
        if ($contentType == 'js') {
            $result[] = '\.payments-amazon\.com/checkout';
        }
        
        return $result;
    }
}
