<?php

namespace Amazon\Pay\Command\Sales;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;    
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Sales\Api\Data\OrderInterface;

class AmazonChargePermissionCommand extends Command {

    const ORDER_ID = 'orderId';

    /** 
     * @var \Magento\Framework\App\State $state
    */
    private $state;

    /**
     * @var \Magento\Sales\Model\OrderRepository $orderRepository
     */
    private $orderRepository;

    /**
     * @var \Amazon\Pay\Model\Adapter\AmazonPayAdapter $amazonAdapter
     */
    private $amazonAdapter;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        \Magento\Framework\App\State $state, 
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Amazon\Pay\Model\Adapter\AmazonPayAdapter $amazonAdapter,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->state = $state;
        $this->orderRepository = $orderRepository;
        $this->amazonAdapter = $amazonAdapter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('amazon:payment:sales:cp-verify');
        $this->addOption(
            self::ORDER_ID,
            null,
            InputOption::VALUE_REQUIRED,
            'OrderId'
        );
        
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        if ($orderId = $input->getOption(self::ORDER_ID)) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(OrderInterface::INCREMENT_ID, $orderId)->create();
            
            $orderResults = $this->orderRepository->getList($searchCriteria)->getItems();
            $order = reset($orderResults);
            $storeId = $order->getStoreId();
            $chargePermissionId = $order->getPayment()->getAdditionalInformation('charge_permission_id');

            try {
                $resp = $this->amazonAdapter->getChargePermission($storeId, $chargePermissionId);
            } catch (\Exception $ex) { }

            $referenceID = $resp['merchantMetadata']['merchantReferenceId'];
            $output->write('<info>' . $referenceID . '</info>');
        }
    }

}
