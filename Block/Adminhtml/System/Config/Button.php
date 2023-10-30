<?php

namespace Amazon\Pay\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Button extends Field
{
    protected $_template = 'Amazon_Pay::system/config/button.phtml';

    /**
     * @param AbstractElement $element
     * @return mixed
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return mixed
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return mixed
     */
    public function getCustomUrl()
    {
        return $this->getUrl('amazon_pay/spc/synctokens');
    }

    /**
     * @return mixed
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData([
                'id' => 'sync_tokens',
                'label' => __('Manually Generate & Sync Tokens'),
                'on_click' => sprintf("location.href = '%s';", $this->getCustomUrl()),
            ]);

        return $button->toHtml();
    }
}
