<?php
/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Amazon\Payment\Controller\Payment;

use Amazon\Core\Exception\AmazonServiceUnavailableException;
use Amazon\Core\Helper\Data as AmazonHelper;
use Amazon\Core\Model\AmazonConfig;
use Amazon\Core\Exception\AmazonWebapiException;
use Amazon\Core\Logger\ExceptionLogger;
use Amazon\Payment\Api\OrderInformationManagementInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\App\ObjectManager;

/**
 * Class CompleteCheckout
 *
 * @package Amazon\Payment\Controller\Payment
 */
class CompleteCheckout extends Action
{

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var AmazonHelper
     */
    private $amazonHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var ExceptionLogger
     */
    private $exceptionLogger;

    /**
     * @var OrderInformationManagementInterface
     */
    private $orderInformationManagement;

    /**
     * CompleteCheckout constructor.
     *
     * @param Context $context
     * @param AmazonConfig $amazonConfig
     * @param AmazonHelper $amazonHelper
     * @param CartManagementInterface $cartManagement
     * @param GuestCartManagementInterface $guestCartManagement
     * @param CheckoutSession $checkoutSession
     * @param Session $session
     * @param PageFactory $pageFactory
     * @param MessageManager $messageManager
     * @param ExceptionLogger $exceptionLogger
     * @param OrderInformationManagementInterface $orderInformationManagement
     */
    public function __construct(
        Context $context,
        AmazonConfig $amazonConfig,
        AmazonHelper $amazonHelper,
        CartManagementInterface $cartManagement,
        GuestCartManagementInterface $guestCartManagement,
        CheckoutSession $checkoutSession,
        Session $session,
        PageFactory $pageFactory,
        MessageManager $messageManager,
        ExceptionLogger $exceptionLogger = null,
        OrderInformationManagementInterface $orderInformationManagement = null
    ) {
        parent::__construct($context);
        $this->amazonConfig = $amazonConfig;
        $this->amazonHelper = $amazonHelper;
        $this->cartManagement = $cartManagement;
        $this->checkoutSession = $checkoutSession;
        $this->session = $session;
        $this->pageFactory = $pageFactory;
        $this->messageManager = $messageManager;
        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
        $this->orderInformationManagement = $orderInformationManagement ?: ObjectManager::getInstance()
            ->get(OrderInformationManagementInterface::class);
    }

    /*
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $authenticationStatus = $this->getRequest()->getParam('AuthenticationStatus');
            // Bypass cache check in \Magento\PageCache\Model\DepersonalizeChecker
            $this->getRequest()->setParams(['ajax' => 1]);
            switch ($authenticationStatus) {
                case 'Success':
                    try {
                        if (!$this->session->isLoggedIn()) {
                            $this->checkoutSession->getQuote()->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
                        }
                        $this->cartManagement->placeOrder($this->checkoutSession->getQuoteId());
                        if ($this->amazonHelper->getAuthorizationMode() == 'synchronous_possible') {
                            $this->messageManager->addNoticeMessage(__(
                                'Your transaction with Amazon Pay is currently being validated. ' .
                                'Please be aware that we will inform you shortly as needed.'
                            ));
                        }
                        return $this->_redirect('checkout/onepage/success');
                    } catch (AmazonWebapiException $e) {
                        if ($this->amazonConfig->isSoftDecline($e->getCode())) {
                            return $this->_redirect('checkout', [
                                '_query' => 'orderReferenceId=' . $this->getOrderReferenceId(),
                                '_fragment' => 'payment',
                            ]);
                        }
                        $this->exceptionLogger->logException($e);
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }
                    break;
                case 'Failure':
                    $this->messageManager->addErrorMessage(__(
                        'Amazon Pay was unable to authenticate the payment instrument.  '
                        . 'Please try again, or use a different payment method.'
                    ));
                    break;
                case 'Abandoned':
                default:
                    $this->messageManager->addErrorMessage(__(
                        'The SCA challenge was not completed successfully.  '
                        . 'Please try again, or use a different payment method.'
                    ));
                    return $this->_redirect('checkout', [
                        '_query' => 'orderReferenceId=' . $this->getOrderReferenceId(),
                        '_fragment' => 'payment',
                    ]);
            }

            $orderReferenceId = $this->getOrderReferenceId();

            if ($orderReferenceId) {
                // Cancel the order to prevent confusion when the merchant views Transactions in Seller Central
                try {
                    $this->orderInformationManagement->cancelOrderReference($orderReferenceId, $this->checkoutSession->getQuote()->getStoreId());
                } catch (AmazonServiceUnavailableException $e) {
                    $this->exceptionLogger->logException($e);
                }
            }

            return $this->_redirect('checkout/cart');
        } catch (\Exception $e) {
            $this->exceptionLogger->logException($e);
            throw $e;
        }
    }

    /**
     * Return Amazon order reference ID
     *
     * @return string
     * @throws NotFoundException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getOrderReferenceId()
    {
        $quote = $this->checkoutSession->getQuote();
        if (!$quote) {
            throw new NotFoundException(__('Failed to retrieve quote from checkoutSession'));
        }
        return $quote
            ->getExtensionAttributes()
            ->getAmazonOrderReferenceId()
            ->getAmazonOrderReferenceId();
    }
}
