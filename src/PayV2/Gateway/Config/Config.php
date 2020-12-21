<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amazon\PayV2\Gateway\Config;

use Amazon\PayV2\Model\AmazonConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const CODE = 'amazon_payment_v2';

    const KEY_ACTIVE = 'active';

    /**
     * @var AmazonConfig
     */
    protected $amazonConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param AmazonConfig $amazonConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        AmazonConfig $amazonConfig,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->amazonConfig = $amazonConfig;
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        parent::__construct($scopeConfig, self::CODE);
    }

    /**
     * @param int|null $storeId
     * @return boolean
     */
    protected function canCapturePartial($storeId = null)
    {
        // get the order store id if not provided
        if (empty($storeId)) {
            $orderId = $this->request->getParam('order_id');
            if ($orderId) {
                $order = $this->orderRepository->get($orderId);
                $storeId = $order->getStoreId();
            }
        }

        $region = $this->amazonConfig->getPaymentRegion(ScopeInterface::SCOPE_STORE, $storeId);
        switch ($region) {
            case 'de':
            case 'uk':
                $result = false;
                break;
            default:
                $result = parent::getValue('can_capture_partial', $storeId);
                break;
        }
        return $result;
    }

    /**
     * Gets Payment configuration status.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool) $this->getValue(self::KEY_ACTIVE, $storeId);
    }

    /**
     * @param string $field
     * @param int|null $storeId
     * @return mixed
     */
    public function getValue($field, $storeId = null)
    {
        switch ($field) {
            case 'can_capture_partial':
                $result = $this->canCapturePartial($storeId);
                break;
            default:
                $result = parent::getValue($field, $storeId);
                break;
        }
        return $result;
    }
}
