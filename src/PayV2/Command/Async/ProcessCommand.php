<?php

namespace Amazon\PayV2\Command\Async;

use Amazon\PayV2\Api\Data\AsyncInterface;
use Magento\Framework\Data\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCommand extends Command
{
    /**
     * @var \Amazon\PayV2\Model\ResourceModel\Async\CollectionFactory
     */
    private $asyncCollectionFactory;

    /**
     * @var \Amazon\PayV2\Model\AsyncUpdater
     */
    private $asyncUpdater;

    public function __construct(
        \Amazon\PayV2\Model\ResourceModel\Async\CollectionFactory $asyncCollectionFactory,
        \Amazon\PayV2\Model\AsyncUpdater $asyncUpdater,
        string $name = null
    )
    {
        $this->asyncCollectionFactory = $asyncCollectionFactory;
        $this->asyncUpdater = $asyncUpdater;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('amazon:payment:async:process');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collection = $this->asyncCollectionFactory->create();
        $collection->addFieldToFilter(AsyncInterface::IS_PENDING, ['eq' => 1]);
        $collection->addOrder(AsyncInterface::ID, Collection::SORT_ORDER_ASC);
        foreach ($collection as $item) {
            /** @var \Amazon\PayV2\Model\Async $item */
            $this->asyncUpdater->processPending($item);
        }
    }
}
