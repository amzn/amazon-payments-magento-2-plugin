<?php

namespace Amazon\Pay\Command\Async;

use Amazon\Pay\Api\Data\AsyncInterface;
use Magento\Framework\Data\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCommand extends Command
{
    /**
     * @var \Amazon\Pay\Model\ResourceModel\Async\CollectionFactory
     */
    private $asyncCollectionFactory;

    /**
     * @var \Amazon\Pay\Model\AsyncUpdater
     */
    private $asyncUpdater;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * ProcessCommand constructor.
     *
     * These dependencies are proxied, update di.xml if changed
     *
     * @param \Amazon\Pay\Model\ResourceModel\Async\CollectionFactory $asyncCollectionFactory
     * @param \Amazon\Pay\Model\AsyncUpdater $asyncUpdater
     * @param \Magento\Framework\App\State $state
     * @param string|null $name
     */
    public function __construct(
        \Amazon\Pay\Model\ResourceModel\Async\CollectionFactory $asyncCollectionFactory,
        \Amazon\Pay\Model\AsyncUpdater $asyncUpdater,
        \Magento\Framework\App\State $state,
        string $name = null
    ) {
        $this->asyncCollectionFactory = $asyncCollectionFactory;
        $this->asyncUpdater = $asyncUpdater;
        $this->state = $state;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('amazon:payment:async:process');
        parent::configure();
    }

    /**
     * Execute asynchronous processing of pending orders
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $collection = $this->asyncCollectionFactory->create();
        $collection->addFieldToFilter(AsyncInterface::IS_PENDING, ['eq' => 1]);
        $collection->addOrder(AsyncInterface::ID, Collection::SORT_ORDER_ASC);
        foreach ($collection as $item) {
            /** @var \Amazon\Pay\Model\Async $item */
            $this->asyncUpdater->processPending($item);
        }

        $code = defined('Command::SUCCESS') ? Command::SUCCESS : 0;
        return $code;
    }
}
