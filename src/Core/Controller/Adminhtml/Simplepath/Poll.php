<?php

namespace Amazon\Core\Controller\Adminhtml\Simplepath;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\System;

class Poll extends System
{


    public function __construct(
        Context $context,
        \Amazon\Core\Model\Config\SimplePath $simplePath,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    )
    {
        parent::__construct($context);
        $this->simplePath = $simplePath;
        $this->scopeConfig = $scopeConfig;
        $this->jsonResultFactory = $jsonResultFactory;

    }


    /**
     * Detect whether Amazon credentials are set (polled by Ajax)
     */
    public function execute()
    {
        // Keypair is destroyed when credentials are saved
        $shouldRefresh = !($this->scopeConfig->getValue(\Amazon\Core\Model\Config\SimplePath::CONFIG_XML_PATH_PUBLIC_KEY, 'default', 0));

        if ($shouldRefresh) {
            $this->simplePath->autoEnable();
        }

        $result = $this->jsonResultFactory->create();
        $result->setData((int)$shouldRefresh);
        return $result;
    }
}
