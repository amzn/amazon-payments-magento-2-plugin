<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amazon\Payment\Gateway\Config;

/**
 * Class Config
 * @package Amazon\Payment\Gateway\Config
 *
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const CODE = 'amazon_payment';

    const KEY_ACTIVE = 'active';

    /**
     * @var \Amazon\Core\Model\AmazonConfig
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
     * Config constructor.
     * @param \Amazon\Core\Model\AmazonConfig $amazonConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Amazon\Core\Model\AmazonConfig $amazonConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
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

        $region = $this->amazonConfig->getPaymentRegion(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
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
