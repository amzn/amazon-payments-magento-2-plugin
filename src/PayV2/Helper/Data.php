<?php

namespace Amazon\PayV2\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Zxing\ReaderException;

class Data extends AbstractHelper
{
    /**
     * @var \Amazon\PayV2\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    private $helperCheckout;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    private $categoryResourceModel;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var mixed
     */
    private $restrictedCategoryIds;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    public function __construct(
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Magento\Checkout\Helper\Data $helperCheckout,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
    )
    {
        $this->amazonConfig = $amazonConfig;
        $this->helperCheckout = $helperCheckout;
        $this->moduleList = $moduleList;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->metadataPool = $metadataPool;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        parent::__construct($context);
    }

    /**
     * Inspired by \Magento\Catalog\Model\ResourceModel\Product\Collection::getChildrenCategories
     *
     * @param int $restrictedCategoryId
     * @return array
     */
    protected function fetchRestrictedCategoryIds($restrictedCategoryId)
    {
        $result[] = $restrictedCategoryId;
        $categories = $this->categoryResourceModel->getCategoryWithChildren($restrictedCategoryId);
        if (!empty($categories)) {
            $firstCategory = array_shift($categories);
            if ($firstCategory['is_anchor'] == 1) {
                $linkfield = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)->getLinkField();
                $anchorCategories[] = $firstCategory[$linkfield];
                foreach ($categories as $category) {
                    if (in_array($category['parent_id'], $result) && in_array($category['parent_id'], $anchorCategories)) {
                        $result[] = $category[$linkfield];
                        if ($category['is_anchor'] == 1 || in_array($category['parent_id'], $anchorCategories)) {
                            $anchorCategories[] = $category[$linkfield];
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    protected function getRestrictedCategoryIds()
    {
        if ($this->restrictedCategoryIds === null) {
            $restrictedCategoryIds = [];
            foreach ($this->amazonConfig->getRestrictedCategoryIds() as $restrictedCategoryId) {
                if (!in_array($restrictedCategoryId, $restrictedCategoryIds)) {
                    $restrictedCategoryIds = array_merge($restrictedCategoryIds, $this->fetchRestrictedCategoryIds($restrictedCategoryId));
                }
            }
            $this->restrictedCategoryIds = $restrictedCategoryIds;
        }
        return $this->restrictedCategoryIds;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function isPayOnly($quote = null)
    {
        if ($quote === null) {
            $quote = $this->helperCheckout->getQuote();
        }
        return $quote->hasItems() ? $quote->isVirtual() : true;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function hasRestrictedProducts($quote = null)
    {
        if ($quote === null) {
            $quote = $this->helperCheckout->getQuote();
        }
        $result = false;
        foreach ($quote->getAllItems() as $item) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            if ($this->isProductRestricted($item->getProduct())) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isProductRestricted($product)
    {
        $productCategoryIds = $product->getCategoryIds();
        $restrictedCategoryIds = $this->getRestrictedCategoryIds();
        return !empty(array_intersect($productCategoryIds, $restrictedCategoryIds));
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        $module = $this->moduleList->getOne('Amazon_PayV2');
        return $module['setup_version'] ?? __('--');
    }

    /**
     * Get module composer version
     *
     * @param $moduleName
     * @return string
     */
    public function getModuleVersion($moduleName)
    {
        $path = $this->componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            $moduleName
        );
        $directoryRead = $this->readFactory->create($path);
        try {
            $composerJsonData = $directoryRead->readFile('composer.json');
        } catch (Exception $e) {
            return '--';
        }
        $data = json_decode($composerJsonData);

        return !empty($data->version) ? $data->version : __('Read error!');
    }
}
