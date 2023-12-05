<?php

namespace Amazon\Pay\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Widget\Button as WidgetButton;

class Button extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Amazon_Pay::system/config/button.phtml';

    /**
     * Render
     *
     * @param AbstractElement $element
     * @return mixed
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Get element html
     *
     * @param AbstractElement $element
     * @return mixed
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Get custom url
     *
     * @return mixed
     */
    public function getCustomUrl()
    {
        return $this->getUrl('amazon_pay/spc/synctokens');
    }

    /**
     * Get button html
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(WidgetButton::class)
            ->setData([
                'id' => 'sync_tokens',
                'label' => __('Manually Generate & Sync Tokens'),
                'on_click' => sprintf("location.href = '%s';", $this->getCustomUrl()),
            ]);

        return $button->toHtml();
    }
}
