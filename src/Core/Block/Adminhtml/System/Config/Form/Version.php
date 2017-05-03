<?php

namespace Amazon\Core\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Module\ModuleListInterface;

class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    const MODULE_AMAZON_CORE = 'Amazon_Core';
    const MODULE_AMAZON_LOGIN = 'Amazon_Login';
    const MODULE_AMAZON_PAYMENT = 'Amazon_Payment';

    protected $_moduleList;

    public function __construct(
        ModuleListInterface $moduleList,
        Context $context,
        array $data = []
    ) {
        $this->_moduleList = $moduleList;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $output = '<div style="background-color:#eee;padding:1em;border:1px solid #ddd;">';
        $output .= 'Extension version: ' . $this->getVersion(self::MODULE_AMAZON_CORE);
        $output .= "</div>";
         return $output;
    }

    protected function getVersion($module)
    {
        $version = $this->_moduleList->getOne($module);
        if ($version && isset($version['setup_version'])) {
            return $version['setup_version'];
        } else {
            return 'Missing!';
        }
    }
}