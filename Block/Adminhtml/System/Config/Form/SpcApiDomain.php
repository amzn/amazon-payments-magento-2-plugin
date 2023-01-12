<?php

namespace Amazon\Pay\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Store\Model\StoreManagerInterface;

class SpcApiDomain extends \Magento\Config\Block\System\Config\Form\Field //\Magento\Framework\Data\Form\Element\Text
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    )
    {
        parent::__construct($context, $data, $secureRenderer);

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
