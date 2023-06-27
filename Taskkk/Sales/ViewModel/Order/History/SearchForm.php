<?php
declare(strict_types=1);

namespace Base\Sales\ViewModel\Order\History;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class SearchForm implements ArgumentInterface
{
    final public const ORDER_NUMBER_SEARCH_NAME = 'order_number';

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly OrderConfig $orderConfig,
        private readonly RequestInterface $request,
        private readonly SessionManagerInterface $customerSession,
        private readonly CollectionFactory $collectionFactory
    ){}

    public function getSearchQuery(): string
    {
        return $this->request->getParam(self::ORDER_NUMBER_SEARCH_NAME, '');
    }

    /**
     * @return bool
     */
    public function hasOrders()
    {
        $customerId      = $this->customerSession->getCustomerId();
        $orderCollection = $this->collectionFactory->create($customerId)->addFieldToFilter(
            'status',
            ['in' => $this->orderConfig->getVisibleOnFrontStatuses()]
        );

        return (bool) $orderCollection->getSize();
    }
}
