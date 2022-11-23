<?php

namespace Amazon\Pay\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\App\ProductMetadataInterface;

class SpcTokenSyncStatus extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = 'Amazon_Pay::system/config/sync-status.phtml';

    protected $storeRepository;

    public function __construct(
        Context $context,
        StoreRepositoryInterface $storeRepository,
        ProductMetadataInterface $productMetadata,
        array $data = []
    )
    {
        // Special case for lower than Magento 2.4 version
        if ($productMetadata->getVersion() < '2.4') {
            parent::__construct($context, $data);
        }
        // Magento 2.4.0+
        else {
            parent::__construct($context, $data, null);
        }

        $this->storeRepository = $storeRepository;
    }

    /**
     * @param AbstractElement $element
     * @return mixed
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return mixed
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getConfigForAllStores()
    {
        $storeValues = [];
        $noSyncMessage = $this->_scopeConfig->getValue('payment/amazon_payment_v2/spc_tokens_sync_status_no');

        foreach ($this->storeRepository->getList() as $store) {
            if ($store->getId() == 0) {
                continue;
            }

            $value = $this->_scopeConfig->getValue(
                'payment/amazon_pay/spc_tokens_sync_status',
                'store',
                $store->getId()) ?? $noSyncMessage;

            $storeValues[] = [
                'store_name' => $store->getName(),
                'value' => $value
            ];
        }

        return $storeValues;
    }
}
