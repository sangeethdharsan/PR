<?php
declare(strict_types=1);

namespace Base\Sales\Api;

use Magento\Sales\Api\Data\OrderInterface;

interface GetLineListFromOrdersInterface
{
    /**
     * @param OrderInterface[] $orders
     * @return mixed
     */
    public function execute(array $orders);
}
