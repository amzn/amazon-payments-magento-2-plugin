<?php

namespace Amazon\Core\Block\Adminhtml\System\Config;

class SimplePathAdmin extends \Magento\Framework\View\Element\Template
{
    /**
     * @var SimplePath
     */
    private $simplePath;

    /**
     * SimplePathAdmin constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Amazon\Core\Model\Config\SimplePath $simplePath
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amazon\Core\Model\Config\SimplePath $simplePath,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->simplePath = $simplePath;
    }

    /**
     * Return SimplePath settings
     */
    public function getAmazonSpJson()
    {
        return json_encode($this->simplePath->getJsonAmazonSpConfig());
    }

    /**
     * Return region
     */
    public function getRegion()
    {
        return $this->simplePath->getRegion();
    }

    /**
     * Return currency
     */
    public function getCurrency()
    {
        return $this->simplePath->getCurrency();
    }
}
