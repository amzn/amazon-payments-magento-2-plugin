<?php

namespace Amazon\Core\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Amazon\Core\Helper\Data as CoreHelper;

class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var CoreHelper
     */
    private $coreHelper;

    /**
     * Version constructor.
     * @param CoreHelper $coreHelper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        CoreHelper $coreHelper,
        array $data = []
    ) {
        $this->coreHelper = $coreHelper;
        parent::__construct($context, $data);
    }

    /**
     * Render element value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $version = $this->coreHelper->getVersion();
        if (!$version) {
            $version = __('--');
        }
        $output = '<div style="background-color:#eee;padding:1em;border:1px solid #ddd;">';
        $output .= __('Module version') . ': ' . $version;
        $output .= "</div>";
        return $output;
    }
}
