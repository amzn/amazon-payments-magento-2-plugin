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
        $errorString = '';
        foreach($this->checkoutSession->getQuote()->getErrors() as $error) {
            $errorString .= $error->getText() . "\n";
        }
        return $errorString;
    }

    public function getCheckoutUrl() {
        return $this->getUrl('checkout');
    }
}