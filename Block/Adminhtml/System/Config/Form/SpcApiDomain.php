<?php

namespace Amazon\Pay\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Store\Model\StoreManagerInterface;

class SpcApiDomain extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ProductMetadataInterface $productMetadata
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ProductMetadataInterface $productMetadata,
        array $data = []
    )
    {
        // Special case for lower than Magento 2.4 version
        if ($productMetadata->getVersion() < '2.4') {
            parent::__construct($context, $data);
        }
        // Magento 2.4.0+
        else {
            parent::__construct($context, $data, null);
        }

        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if (empty($element->getValue())) {
            $element->setValue($this->storeManager->getStore(0)->getBaseUrl());
        }

        return $element->getElementHtml();
    }
}
