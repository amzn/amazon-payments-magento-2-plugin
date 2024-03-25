<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
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

namespace Amazon\Pay\Command\Sales;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Sales\Api\Data\OrderInterface;

class AmazonChargePermissionCommand extends Command
{

    private const ORDER_ID = 'orderId';

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

    /**
     * AmazonChargePermissionCommand constructor.
     *
     * These dependencies are proxied, update di.xml if changed
     *
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Amazon\Pay\Model\Adapter\AmazonPayAdapter $amazonAdapter
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
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

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('amazon:payment:sales:verify-charge-permission');
        $this->addOption(
            self::ORDER_ID,
            null,
            InputOption::VALUE_REQUIRED,
            'OrderId'
        );
        
        parent::configure();
    }

    /**
     * Execute charge permission retrieval by order ID
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        if ($orderId = $input->getOption(self::ORDER_ID)) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(OrderInterface::INCREMENT_ID, $orderId)->create();
            
            $orderResults = $this->orderRepository->getList($searchCriteria)->getItems();
            if (empty($orderResults)) {
                $output->writeln('<info>No order found for order number ' . $orderId . '</info>');
                return;
            }
            
            $order = reset($orderResults);
            $storeId = $order->getStoreId();
            $chargePermissionId = $order->getPayment()->getAdditionalInformation('charge_permission_id');

            try {
                $resp = $this->amazonAdapter->getChargePermission($storeId, $chargePermissionId);
            } catch (\Exception $ex) {
                $output->writeLn('<error>Error retrieving charge permission from Amazon.</error>');
            }

            if (isset($resp['merchantMetadata']['merchantReferenceId'])) {
                $referenceID = $resp['merchantMetadata']['merchantReferenceId'];
            }
        
            $verified = $referenceID == $orderId;
            $output->writeln(var_export($verified));
        }
        
        return Command::SUCCESS;
    }
}
