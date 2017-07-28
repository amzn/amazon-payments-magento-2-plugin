<?php

namespace Amazon\Core\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Module\ModuleListInterface;

class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    const MODULE_AMAZON_CORE = 'Amazon_Core';
    const MODULE_AMAZON_LOGIN = 'Amazon_Login';
    const MODULE_AMAZON_PAYMENT = 'Amazon_Payment';

    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * Version constructor.
     * @param ModuleListInterface $moduleList
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ModuleListInterface $moduleList,
        Context $context,
        array $data = []
    ) {
        $this->_moduleList = $moduleList;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $output = '<div style="background-color:#eee;padding:1em;border:1px solid #ddd;">';
        $output .= __('Module version') . ': ' . $this->getVersion(self::MODULE_AMAZON_CORE);
        $output .= "</div>";
         return $output;
    }

    /**
     * @param $module
     * @return \Magento\Framework\Phrase
     */
    protected function getVersion($module)
    {
        /*
        $version = $this->_moduleList->getOne($module);
        if ($version && isset($version['setup_version'])) {
            return $version['setup_version'];
        } else {
            return __('--');
        }
        */
        return "1.1.3";
    }
}
