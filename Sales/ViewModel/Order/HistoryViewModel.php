<?php
declare(strict_types=1);

namespace Base\Sales\ViewModel\Order;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Base\Sales\Helper\Config;
use Base\Sales\Api\GetLineListFromOrdersInterface;

class HistoryViewModel implements ArgumentInterface
{
    /**
     * @param Config $config
     * @param GetLineListFromOrdersInterface $getLineListFromOrders
     */
    public function __construct(
        private readonly Config $config,
        private readonly GetLineListFromOrdersInterface $getLineListFromOrders
    ){}

    /**
     * Returns order return url
     */
    public function getReturnOrderUrl(OrderInterface $order): ?string
    {
        $urlTemplate = $this->config->getReturnServiceUrlTemplate();
        if ($urlTemplate) {
            return sprintf(
                $urlTemplate,
                $order->getIncrementId(),
                str_replace(' ', '', $order->getShippingAddress()->getPostcode())
            );
        }
        return null;
    }

    /**
     * Check if return service enabled
     */
    public function isReturnServiceEnabled(): bool
    {
        return $this->config->isReturnServiceEnabled();
    }

    /**
     * Check if return service available for order
     */
    public function isReturnServiceAvailable(OrderInterface $order): bool
    {
        return $this->isReturnServiceEnabled() &&
            $this->getReturnOrderUrl($order) && $order->hasShipments();
    }

    /**
     * Fix the empty image issue when we have an old product_id in the sales_order_item for migrated orders
     *
     * @return array|null
     */
    public function getLineListFromOrders(OrderCollection $orders)
    {
        return $this->getLineListFromOrders->execute($orders->getItems());
    }

    public function isFedexTrackingAvailable(OrderInterface $order): bool
    {
        return $this->isFedexTrackingEnabled() &&
            $this->getFedexTrackingUrl($order);
    }

    /**
     * Check if return service enabled
     */
    public function isFedexTrackingEnabled(): bool
    {
        return $this->config->isFedexTrackingEnabled();
    }

    /**
     * Returns fedex trackig url
     *
     * @param string $fedexTrackingNumber
     */
    public function getFedexTrackingUrl(OrderInterface $order): ?string
    {
        $fedexTrackingNumber = $this->getTrackingNumberFromOrder($order);
        $urlTemplate = $this->config->getFedexTrackingUrlTemplate();
        if ($urlTemplate && $fedexTrackingNumber !== null) {
            return sprintf(
                $urlTemplate,
                $fedexTrackingNumber
            );
        }
        return null;
    }

    /**
     * Get tracking number from order shipment
     */
    public function getTrackingNumberFromOrder(OrderInterface $order): ?string
    {
        $trackingNumber = [];
        if ($order) {

            foreach ($order->getTracksCollection() as $track) {
                $trackingNumber[] = $track->getNumber();
            }

            /** Return first tracking number */
            if (!empty($trackingNumber)) {
                return $trackingNumber[0];
            }
        }

        return null;
    }
}
