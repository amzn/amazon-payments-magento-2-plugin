<?php
namespace Amazon\Payment\Block;
use Magento\Framework\View\Element\Template;
use \Magento\Checkout\Model\Session as CheckoutSession;
use \Magento\Framework\View\Element\Template\Context;

class CheckoutErrorMessage extends Template
{
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
    }
    protected function _prepareLayout()
    {
    }

    public function getError() {
        return $this->checkoutSession->getQuote()->getError();
    }

    public function getCheckoutUrl() {
        return $this->getUrl('checkout');
    }
}