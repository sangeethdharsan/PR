<?php
declare(strict_types=1);

namespace Base\Sales\ViewModel\Order\Items\Render;

use Base\Checkout\Api\Service\GetFullSizeByProductInterface;
use Base\ImageLogicServiceClient\Helper\Image as ImageHelper;
use Base\Sales\Api\GetLineListFromOrdersInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Registry;

class Item implements ArgumentInterface
{
    final const SIZE_ATTRIBUTE_CODE = 'size';

    final const COLOR_ATTRIBUTE_CODE = 'color';

    /**
     * @var ?string
     */
    private $colorAttributeId;

    /**
     * @var ?string
     */
    private $sizeAttributeId;

    /**
     * @param Attribute $eavAttribute
     * @param GetFullSizeByProductInterface $getFullSizeByProduct
     * @param ImageHelper $imageHelper
     * @param GetLineListFromOrdersInterface $getLineListFromOrders
     * @param Registry $coreRegistry
     */
    public function __construct(
        private readonly Attribute $eavAttribute,
        private readonly GetFullSizeByProductInterface $getFullSizeByProduct,
        private readonly ImageHelper $imageHelper,
        private readonly GetLineListFromOrdersInterface $getLineListFromOrders,
        private readonly Registry $coreRegistry
    ){}

    public function getColor(DataObject $item, array $productOptions = []): string
    {
        if (!$productOptions) {
            return '';
        }

        $colorAttributeId = $this->getColorAttributeId();
        $color = array_filter($productOptions, fn($option) => isset($option['option_id']) && $option['option_id'] === (int) $colorAttributeId);

        if ($color) {
            $color = array_shift($color);
            return $color['value'] ?? '';
        }

        $additionalInfo = $item->getProductOptionByCode('additional_product_info');
        if ($additionalInfo && isset($additionalInfo['color'])) {
            return $additionalInfo['color'];
        }

        return '';
    }

    public function getSize(DataObject $item, array $productOptions = []): string
    {
        $product = null;

        if ($item->getProductType() === Configurable::TYPE_CODE && $item->getChildrenItems()) {
            $children = $item->getChildrenItems();
            $product = isset($children[0]) ? $children[0]->getProduct() : null;
        } else {
            $product = $item->getProduct();
        }

        if ($product) {
            $fullSize = $this->getFullSizeByProduct->execute($product);

            if ($fullSize) {
                return $fullSize;
            }
        }

        if (!$productOptions) {
            return '';
        }


        $sizeAttributeId = $this->getSizeAttributeId();
        $size = array_filter($productOptions, fn($item) => isset($item['option_id']) && $item['option_id'] === (int) $sizeAttributeId);

        if ($size) {
            $size = array_shift($size);
            return $size['value'] ?? '';
        }

        $additionalInfo = $item->getProductOptionByCode('additional_product_info');
        if ($additionalInfo && isset($additionalInfo['size'])) {
            return $additionalInfo['size'] == 'No' ? '' : $additionalInfo['size'];
        }

        return '';
    }

    /**
     * @return int[]
     */
    public function getExcludedOptions(): array
    {
        return [(int) $this->getColorAttributeId(), (int) $this->getSizeAttributeId()];
    }

    public function getProductImage(string $line, string $productName): string
    {
        return $this->imageHelper->getCheckoutImage($line, $productName);
    }

    /**
     * Get default image placeholder url
     */
    public function getImagePlaceholderUrl(): string
    {
        return $this->imageHelper->getDefaultPlaceholderUrl('image');
    }

    /**
     * Fix the empty image issue when we have an old product_id in the sales_order_item for migrated orders
     *
     * @return array|null
     */
    public function getLineListFromOrders()
    {
        $order = $this->getOrder();

        if (!$order) {
            return [];
        }

        return $this->getLineListFromOrders->execute([$order]);
    }

    /**
     * @return int|string|null
     */
    private function getColorAttributeId()
    {
        if (!$this->colorAttributeId) {
            $this->colorAttributeId = $this->eavAttribute->getIdByCode(Product::ENTITY, self::COLOR_ATTRIBUTE_CODE);
        }

        return $this->colorAttributeId;
    }

    /**
     * @return int|string|null
     */
    private function getSizeAttributeId()
    {
        if (!$this->sizeAttributeId) {
            $this->sizeAttributeId = $this->eavAttribute->getIdByCode(Product::ENTITY, self::SIZE_ATTRIBUTE_CODE);
        }

        return $this->sizeAttributeId;
    }

    /**
     * Retrieve current order model instance
     *
     * @return OrderInterface
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }
}
