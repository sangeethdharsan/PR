<?php
declare(strict_types=1);

namespace Base\Sales\Block\Order;

use Base\Checkout\Api\Service\GetEstimatedDeliveryDateWithFormatInterface;
use Base\OrderStatusServiceClient\Model\Service\OrderStatusServiceClient;
use Base\Sales\ViewModel\Order\History\SearchForm;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Block\Order\History as MagentoHistory;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Base\Sales\Api\DateFormatInterface;
use Base\Mnp\Helper\Data;

class History extends MagentoHistory implements DateFormatInterface
{
    /**
     * @param Context $context
     * @param CollectionFactory $orderCollectionFactory
     * @param Session $customerSession
     * @param Config $orderConfig
     * @param OrderStatusServiceClient $orderStatusServiceClient
     * @param GetEstimatedDeliveryDateWithFormatInterface $getEstimatedDeliveryDateWithFormat
     * @param Data $mnpHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $orderCollectionFactory,
        Session $customerSession,
        Config $orderConfig,
        private readonly OrderStatusServiceClient $orderStatusServiceClient,
        private readonly RequestInterface $request,
        private readonly GetEstimatedDeliveryDateWithFormatInterface $getEstimatedDeliveryDateWithFormat,
        private readonly Data $mnpHelper,
        array $data = []
    ) {
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);
    }

    /**
     * @param int $id
     * @return string
     */
    public function getOrderStatus($id)
    {
        return $this->orderStatusServiceClient->fetchOrderStatus($id);
    }

    public function hasSearchQuery(): bool
    {
        return (bool) $this->request->getParam(SearchForm::ORDER_NUMBER_SEARCH_NAME, '');
    }

    /**
     * @inheritDoc
     */
    protected function _prepareLayout(): self
    {
        parent::_prepareLayout();
        if ($this->getOrders()) {
            $pager = $this->getChildBlock('pager');
            /** @var $pager \Magento\Theme\Block\Html\Pager */
            $pager->setFrameLength(10);
        }

        return $this;
    }

    public function getEstimatedDeliveryDate(OrderInterface $order): ?string
    {
        return $this->getEstimatedDeliveryDateWithFormat->execute($order, self::ORDER_DATE_FORMAT);
    }

    public function getOrderCreationFormattedDate(OrderInterface $order): string
    {
        $createdOrderDate = $order->getCreatedAt();
        if (!$createdOrderDate) {
           return '';
        }

        $orderDate = new \DateTime($createdOrderDate);

        return $orderDate->format(self::ORDER_DATE_FORMAT);
    }

    /**
     * Get Tracking Url
     */
    public function getTrackingUrl(OrderInterface $order): ?string
    {
        return $this->mnpHelper->getTrackingUrl($order);
    }

    /**
     * Get Toggle Value
     */
    public function isDeliveryTrackerToggleEnabled(): bool
    {
        return $this->mnpHelper->isDeliveryTrackerToggleEnabled();
    }
}
