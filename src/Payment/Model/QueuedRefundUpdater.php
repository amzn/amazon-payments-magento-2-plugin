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
namespace Amazon\Payment\Model;

use Amazon\Core\Client\ClientFactoryInterface;
use Amazon\Payment\Api\Data\PendingRefundInterface;
use Amazon\Payment\Api\Data\PendingRefundInterfaceFactory;
use Amazon\Payment\Domain\AmazonRefundDetailsResponseFactory;
use Amazon\Payment\Domain\AmazonRefundStatus;
use Amazon\Payment\Domain\Details\AmazonRefundDetails;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class QueuedRefundUpdater
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

    /**
     * @var ClientFactoryInterface
     */
    private $amazonHttpClientFactory;

    /**
     * @var AmazonRefundDetailsResponseFactory
     */
    private $amazonRefundDetailsResponseFactory;

    /**
     * @var NotifierInterface
     */
    private $adminNotifier;

    /**
     * @var PendingRefundInterfaceFactory
     */
    private $pendingRefundFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $throwExceptions = false;

    /**
     * @param OrderRepositoryInterface           $orderRepository
     * @param OrderPaymentRepositoryInterface    $orderPaymentRepository
     * @param ClientFactoryInterface             $amazonHttpClientFactory
     * @param AmazonRefundDetailsResponseFactory $amazonRefundDetailsResponseFactory
     * @param NotifierInterface                  $adminNotifier
     * @param PendingRefundInterfaceFactory      $pendingRefundFactory
     * @param StoreManagerInterface              $storeManager
     * @param LoggerInterface                    $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        ClientFactoryInterface $amazonHttpClientFactory,
        AmazonRefundDetailsResponseFactory $amazonRefundDetailsResponseFactory,
        NotifierInterface $adminNotifier,
        PendingRefundInterfaceFactory $pendingRefundFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->orderRepository                    = $orderRepository;
        $this->orderPaymentRepository             = $orderPaymentRepository;
        $this->amazonHttpClientFactory            = $amazonHttpClientFactory;
        $this->amazonRefundDetailsResponseFactory = $amazonRefundDetailsResponseFactory;
        $this->adminNotifier                      = $adminNotifier;
        $this->pendingRefundFactory               = $pendingRefundFactory;
        $this->storeManager                       = $storeManager;
        $this->logger                             = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function setThrowExceptions($throwExceptions)
    {
        $this->throwExceptions = $throwExceptions;

        return $this;
    }

    /**
     * @param int $pendingRefundId
     *
     * @return void
     */
    public function checkAndUpdateRefund($pendingRefundId, AmazonRefundDetails $refundDetails = null)
    {
        try {
            $pendingRefund = $this->pendingRefundFactory->create();
            $pendingRefund->getResource()->beginTransaction();
            $pendingRefund->setLockOnLoad(true);
            $pendingRefund->load($pendingRefundId);

            if ($pendingRefund->getRefundId()) {
                $order = $this->orderRepository->get($pendingRefund->getOrderId());

                $storeId = $order->getStoreId();
                $this->storeManager->setCurrentStore($storeId);

                if (null === $refundDetails) {
                    $responseParser = $this->amazonHttpClientFactory->create($storeId)->getRefundDetails([
                        'amazon_refund_id' => $pendingRefund->getRefundId()
                    ]);

                    $response      = $this->amazonRefundDetailsResponseFactory->create(['response' => $responseParser]);
                    $refundDetails = $response->getDetails();
                }

                $status = $refundDetails->getRefundStatus();

                switch ($status->getState()) {
                    case AmazonRefundStatus::STATE_COMPLETED:
                        $pendingRefund->delete();
                        break;
                    case AmazonRefundStatus::STATE_DECLINED:
                        $this->triggerAdminNotificationForDeclinedRefund($pendingRefund);
                        $pendingRefund->delete();
                        break;
                }
            }

            $pendingRefund->getResource()->commit();
        } catch (\Exception $e) {
            $this->logger->error($e);
            $pendingRefund->getResource()->rollBack();

            if ($this->throwExceptions) {
                throw $e;
            }
        }
    }

    /**
     * @param PendingRefundInterface $pendingRefund
     *
     * @return void
     */
    protected function triggerAdminNotificationForDeclinedRefund(PendingRefundInterface $pendingRefund)
    {
        $this->adminNotifier->addMajor(
            'Amazon Pay has declined a refund',
            "Refund ID {$pendingRefund->getRefundId()} for Order ID {$pendingRefund->getOrderId()} " .
            "has been declined by Amazon Pay."
        );
    }
}
