<?php

namespace Amazon\Core\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;

class SimplepathConfig extends \Magento\Config\Block\System\Config\Form\Field
{

    protected $_layout;

    /**
     * Version constructor.
     * @param ModuleListInterface $moduleList
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        Context $context,
        array $data = []
    ) {
        $this->_layout = $layout;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_layout
            ->createBlock('Amazon\Core\Block\Adminhtml\System\Config\SimplePathAdmin')
            ->setTemplate('Amazon_Core::system/config/simplepath_admin.phtml')
            ->setCacheable(false)
            ->toHtml();

        return $html;
    }

}
