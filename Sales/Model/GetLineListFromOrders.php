<?php
declare(strict_types=1);

namespace Base\Sales\Model;

use Base\Sales\Api\GetLineListFromOrdersInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;

class GetLineListFromOrders implements GetLineListFromOrdersInterface
{
    /**
     * @var string
     */
    private const LINE_ATTRIBUTE_CODE = 'line';

    /**
     * @var null|array
     */
    private $lineList = null;

    /**
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(
        protected CollectionFactory $productCollectionFactory
    ){}

    /**
     * @inheritDoc
     */
    public function execute(array $orders)
    {
        if (!$orders) {
            return [];
        }

        if ($this->lineList !== null) {
            return $this->lineList;
        }

        $skus = [];
        $this->lineList = [];

        foreach ($orders as $order) {
            $items = $order->getAllItems();
            foreach ($items as $item) {
                if (!in_array($item->getSku(), $skus)) {
                    $skus[] = $item->getSku();
                }
            }
        }

        if ($skus) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect(self::LINE_ATTRIBUTE_CODE);
            $collection->addFieldToFilter('sku', ['in' => $skus]);

            foreach ($collection->getItems() as $product) {
                if ($product->getLine()) {
                    $this->lineList[$product->getSku()] = $product->getLine();
                }
            }
        }

        return $this->lineList;
    }
}
