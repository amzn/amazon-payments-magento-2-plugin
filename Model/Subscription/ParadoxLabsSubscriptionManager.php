<?php 
namespace Amazon\Pay\Model\Subscription;

class ParadoxLabsSubscriptionManager implements SubscriptionManagerInterface
{

	/**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;


    public function __construct(
    	\Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    	$this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function hasSubscription($quote)
    {
    	return $this->quoteManager->quoteContainsSubscription($quote);
    }

    public function getFrequencyUnit($item)
    {
    	return $this->itemManager->getFrequencyUnit($item);
    }

    public function getFrequencyCount($item)
    {
    	return $this->itemManager->getFrequencyCount($item);
    }

    public function isSubscription($item)
    {
    	return $this->itemManager->isSubscription($item);
    }

    public function cancel($order, $subscription = false) 
	{
		$this->searchCriteriaBuilder->addFilter('keyword_fulltext', '%' . $order->getIncrementId(), 'like');
        $subscriptions = $this->subscriptionRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        if (!empty($subscriptions)) {
            $subscription = array_shift($subscriptions);
            $subscription->setStatus('canceled');
            $this->subscriptionRepository->save($subscription);
        }
	} 

}