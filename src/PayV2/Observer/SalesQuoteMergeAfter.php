<?php

namespace Amazon\PayV2\Observer;

class SalesQuoteMergeAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Amazon\PayV2\Api\CheckoutSessionRepositoryInterface
     */
    private $checkoutSessionRepository;

    public function __construct(\Amazon\PayV2\Api\CheckoutSessionRepositoryInterface $checkoutSessionRepository)
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
