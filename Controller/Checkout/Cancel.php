<?php

namespace Amazon\Pay\Controller\Checkout;

use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Amazon\Pay\Gateway\Config\Config;

class Cancel implements HttpGetActionInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var Session
     */
    protected $magentoCheckoutSession;

    /**
     * @var CheckoutSessionManagement
     */
    protected $checkoutSessionManagement;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param Session $magentoCheckoutSession
     * @param CheckoutSessionManagement $checkoutSessionManagement
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        Session $magentoCheckoutSession,
        CheckoutSessionManagement $checkoutSessionManagement,
        ManagerInterface $messageManager
    )
    {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->magentoCheckoutSession = $magentoCheckoutSession;
        $this->checkoutSessionManagement = $checkoutSessionManagement;
        $this->messageManager = $messageManager;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $redirectParam = $this->request->getParam('redirect');
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        // redirect to cart if no redirect param provided
        if (empty($redirectParam)) {
            return $result->setPath('checkout/cart');
        }

        // check if there is an order still in the session, then and cancel it
        if ($order = $this->magentoCheckoutSession->getLastRealOrder()) {
            if (!empty($order->getData())
                && $order->getState() !== Order::STATE_CANCELED
                && $order->getPayment()
                && ($order->getPayment()->getMethod() === Config::CODE || $order->getPayment()->getMethod() === Config::VAULT_CODE)
            ) {
                $quote = $this->magentoCheckoutSession->getQuote();

                if (!$quote->getIsActive()) {
                    $this->checkoutSessionManagement->cancelOrder($order, $quote);

                    $this->magentoCheckoutSession->restoreQuote();

                    $this->messageManager->addErrorMessage(__('This transaction was cancelled. Please try again.'));
                }
            }
        }
        
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $result->setUrl(base64_decode($redirectParam));
    }
}
