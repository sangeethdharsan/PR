<?php
declare(strict_types=1);

namespace Base\Sales\Plugin\Block\Order\History;

use Base\Sales\Api\Data\SanitizerInterface;
use Base\Sales\ViewModel\Order\History\SearchForm;
use Magento\Sales\Block\Order\History;
use Magento\Framework\App\RequestInterface;

class SearchByOrderNumber
{
    /**
     * @param RequestInterface $request
     * @param SanitizerInterface $sanitizer
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly SanitizerInterface $sanitizer
    ){}

    /**
     * @param $result
     * @return Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function afterGetOrders(History $subject, $result)
    {
        $orderNumber = $this->getSearchQuery();

        if ($result && $orderNumber) {
            /** @var Magento\Sales\Model\ResourceModel\Order\Collection $result */
            $result->addFieldToFilter('increment_id', ['like' => '%' . $orderNumber . '%']);
        }

        return $result;
    }

    public function getSearchQuery(): string
    {
        return $this->sanitizer->sanitizeSearchText($this->request->getParam(SearchForm::ORDER_NUMBER_SEARCH_NAME, ''));
    }
}
