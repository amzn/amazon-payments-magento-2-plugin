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

use Amazon\Core\Model\AmazonConfig;
use Amazon\Core\Exception\AmazonWebapiException;
use Amazon\Core\Logger\ExceptionLogger;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
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
     * CompleteCheckout constructor.
     *
     * @param Context $context
     * @param AmazonConfig $amazonConfig
     * @param CartManagementInterface $cartManagement
     * @param GuestCartManagementInterface $guestCartManagement
     * @param CheckoutSession $checkoutSession
     * @param Session $session
     * @param PageFactory $pageFactory
     * @param MessageManager $messageManager
     * @param ExceptionLogger $exceptionLogger
     */
    public function __construct(
        Context $context,
        AmazonConfig $amazonConfig,
        CartManagementInterface $cartManagement,
        GuestCartManagementInterface $guestCartManagement,
        CheckoutSession $checkoutSession,
        Session $session,
        PageFactory $pageFactory,
        MessageManager $messageManager,
        ExceptionLogger $exceptionLogger = null
    ) {
        parent::__construct($context);
        $this->amazonConfig = $amazonConfig;
        $this->cartManagement = $cartManagement;
        $this->checkoutSession = $checkoutSession;
        $this->session = $session;
        $this->pageFactory = $pageFactory;
        $this->messageManager = $messageManager;
        $this->exceptionLogger = $exceptionLogger ?: ObjectManager::getInstance()->get(ExceptionLogger::class);
    }

    /*
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $authenticationStatus = $this->getRequest()->getParam('AuthenticationStatus');
            switch ($authenticationStatus) {
                case 'Success':
                    try {
                        if (!$this->session->isLoggedIn()) {
                            $this->checkoutSession->getQuote()->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
                        }
                        $this->cartManagement->placeOrder($this->checkoutSession->getQuoteId());
                        return $this->_redirect('checkout/onepage/success');
                    } catch (AmazonWebapiException $e) {
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
            }
            return $this->_redirect('checkout/cart');
        } catch(\Exception $e) {
            $this->exceptionLogger->logException($e);
            throw $e;
        }
    }
}
