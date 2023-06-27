<?php
declare(strict_types=1);

namespace Base\Sales\ViewModel\Order\History;

use Base\Sales\Api\Data\SanitizerInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Session\SessionManagerInterface;

class OrdersNumber implements ArgumentInterface
{
    private ?OrderCollection $orders = null;

    /**
     * @param SessionManagerInterface $customerSession
     * @param CollectionFactory $collectionFactory
     * @param OrderConfig $orderConfig
     * @param RequestInterface $request
     * @param SanitizerInterface $sanitizer
     */
    public function __construct(
        private readonly SessionManagerInterface $customerSession,
        private readonly CollectionFactory $collectionFactory,
        private readonly OrderConfig $orderConfig,
        private readonly RequestInterface $request,
        private readonly SanitizerInterface $sanitizer
    ){}

    public function getOrdersCount(): int
    {
        $customerId = $this->customerSession->getCustomerId();

        if ($customerId === null) {
            return 0;
        }

        return (int) $this->getOrders()->getSize();
    }

    private function getOrders(): OrderCollection
    {
        $customerId = $this->customerSession->getCustomerId();

        if ($this->orders === null && $customerId !== null) {
            $this->orders = $this->collectionFactory->create($customerId)->addFieldToFilter(
                'status',
                ['in' => $this->orderConfig->getVisibleOnFrontStatuses()]
            )->setOrder(
                'created_at',
                'desc'
            );

            $orderNumber = $this->getSearchQuery();

            if ($orderNumber) {
                $this->orders->addFieldToFilter('increment_id', ['like' => '%' . $orderNumber . '%']);
            }
        }

        return $this->orders;
    }

    private function getSearchQuery(): string
    {
        return $this->sanitizer->sanitizeSearchText($this->request->getParam(SearchForm::ORDER_NUMBER_SEARCH_NAME, ''));
    }
}

