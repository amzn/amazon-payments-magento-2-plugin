<?php

namespace Amazon\Pay\Controller\Adminhtml\Spc;

use Amazon\Pay\Model\Spc\AuthTokens;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;

class SyncTokens extends \Magento\Backend\App\Action
{
    /**
     * @var AuthTokens
     */
    protected $authTokens;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @param Context $context
     * @param AuthTokens $authTokens
     * @param ManagerInterface $messageManager
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        Context $context,
        AuthTokens $authTokens,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory
    )
    {
        parent::__construct($context);

        $this->authTokens = $authTokens;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $this->authTokens->createOrRenewAndSendTokens();

            $this->messageManager->addSuccessMessage(
                __('Single Page Checkout tokens synced successfully.')
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Single Page Checkout tokens could not be synced: ') . $e->getMessage()
            );
        }

        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setRefererUrl();

        return $redirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed();
    }
}
