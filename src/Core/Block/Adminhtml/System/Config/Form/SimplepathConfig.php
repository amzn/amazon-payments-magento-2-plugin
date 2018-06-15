<?php

namespace Amazon\Core\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;

class SimplepathConfig extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_layout
            ->createBlock(\Amazon\Core\Block\Adminhtml\System\Config\SimplePathAdmin::class)
            ->setTemplate('Amazon_Core::system/config/simplepath_admin.phtml')
            ->setCacheable(false)
            ->toHtml();

        return $html;
    }
}
