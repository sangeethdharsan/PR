<?php
declare(strict_types=1);

namespace Base\Sales\Model\Order\Invoice\Total;

use Psr\Log\LoggerInterface;
use Base\FeatureToggle\Helper\Toggle;
use Magento\Sales\Model\Order\Invoice\Total\Shipping as SalesShipping;
use Magento\Sales\Model\Order\Invoice;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Order invoice shipping total calculation model
 */
class Shipping extends SalesShipping
{
    /**
     * @param LoggerInterface $logger
     * @param Toggle $toggle
     * @param SerializerInterface $serializer
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected Toggle $toggle,
        private readonly SerializerInterface $serializer
    ){}

    /**
     * @return $this
     */
    public function collect(Invoice $invoice): self
    {
        $invoice->setShippingAmount(0);
        $invoice->setBaseShippingAmount(0);
        $orderShippingAmount = $invoice->getOrder()->getShippingAmount();
        $baseOrderShippingAmount = $invoice->getOrder()->getBaseShippingAmount();
        $shippingInclTax = $invoice->getOrder()->getShippingInclTax();
        $baseShippingInclTax = $invoice->getOrder()->getBaseShippingInclTax();
        $this->addLog($invoice, "Before checking Order shipping amount", $orderShippingAmount);
        if ($orderShippingAmount) {
            $this->addLog($invoice, "After checking Order shipping amount", $orderShippingAmount);
            /**
             * Check shipping amount in previous invoices
             */
            foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
                if ((double)$previousInvoice->getShippingAmount() && !$previousInvoice->isCanceled()) {
                    $this->addPrevInvoiceLog($invoice, $previousInvoice, "Inside previous invoice exit condition");
                    return $this;
                }
                $this->addLog($invoice, "Invoice collection iteration for previous invoices", $previousInvoice->getData());
            }
            $invoice->setShippingAmount($orderShippingAmount);
            $invoice->setBaseShippingAmount($baseOrderShippingAmount);
            $invoice->setShippingInclTax($shippingInclTax);
            $invoice->setBaseShippingInclTax($baseShippingInclTax);

            $invoice->setGrandTotal($invoice->getGrandTotal() + $orderShippingAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseOrderShippingAmount);
            $this->addLog($invoice, "After setting shipping amount for the invoice - final step before saving",$invoice->getData());
        }
        return $this;
    }

    /**
     * @param $invoice
     * @param $message
     * @param array|null $data
     */
    private function addLog($invoice, $message, $data = null)
    {
        if ($this->toggle->variation('invoice-shipping-debug-logging')) {
            $this->logger->info(sprintf("$message for Order# %s is %s", $invoice->getOrder()->getIncrementId(), $this->serializer->serialize($data)));
        }
    }

    /**
     * @param $currentInvoice
     * @param $previousInvoice
     * @param $message
     */
    private function addPrevInvoiceLog($currentInvoice, $previousInvoice, $message)
    {
        if ($this->toggle->variation('invoice-shipping-debug-logging')) {
            $this->logger->info(sprintf("$message for Order# %s & previous Invoice# %s (Order# %s) is %s", $currentInvoice->getOrder()->getIncrementId(), $previousInvoice->getIncrementId(), $previousInvoice->getOrder()->getIncrementId(), $this->serializer->serialize($previousInvoice->getData())));
        }
    }
}
