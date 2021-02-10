<?php

namespace Amazon\Pay\Observer;

class SalesQuoteMergeAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Amazon\Pay\Api\CheckoutSessionRepositoryInterface
     */
    private $checkoutSessionRepository;

    public function __construct(\Amazon\Pay\Api\CheckoutSessionRepositoryInterface $checkoutSessionRepository)
    {
        $this->checkoutSessionRepository = $checkoutSessionRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $source = $observer->getEvent()->getData('source');
        /** @var \Magento\Quote\Api\Data\CartInterface $source */
        $sourceSession = $this->checkoutSessionRepository->getActiveForCart($source);
        if ($sourceSession) {
            $quote = $observer->getEvent()->getData('quote');
            /** @var \Magento\Quote\Api\Data\CartInterface $quote */
            $quoteSession = $this->checkoutSessionRepository->getActiveForCart($quote);
            if ($quoteSession) {
                $quoteSession->cancel();
                $this->checkoutSessionRepository->save($quoteSession);
            }
            $sourceSession->setQuoteId($quote->getId());
            $this->checkoutSessionRepository->save($sourceSession);
        }
    }
}
