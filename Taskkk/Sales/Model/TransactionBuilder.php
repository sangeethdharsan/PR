<?php
declare(strict_types=1);

namespace Base\Sales\Model;

use Magento\Sales\Model\Order\Payment\Transaction\Builder;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\AbstractModel;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Base\FeatureToggle\Api\Options\TransactionBuilderLoggingInterface;

class TransactionBuilder extends Builder
{
    /**
     * @param TransactionRepositoryInterface $transactionRepository
     * @param LoggerInterface $logger
     * @param TransactionBuilderLoggingInterface $transactionBuilderLoggingToggle
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        private readonly LoggerInterface $logger,
        private readonly TransactionBuilderLoggingInterface $transactionBuilderLoggingToggle
    ) {
        parent::__construct($transactionRepository);
    }

    /**
     * @param string $type
     */
    public function build($type): ?TransactionInterface
    {
        if ($this->transactionBuilderLoggingToggle->isTransactionBuilderLoggingEnabled()) {
            $this->logger->info("Transaction Debug - inside the TransactionBuilder::build method");
            $this->logger->info(sprintf(
                "Transaction Debug - payment exist flag = %s and transaction id = %s",
                $this->isPaymentExists(),
                $this->transactionId
            ));
        }
        if ($this->isPaymentExists() && $this->transactionId !== null) {
            if ($this->transactionBuilderLoggingToggle->isTransactionBuilderLoggingEnabled()) {
                $this->logger->info(sprintf(
                    "Transaction Debug - trigger getByTransactionId method. payment id = %s and order id = %s",
                    $this->payment->getId(),
                    $this->order->getId()
                ));
            }

            $transaction = $this->transactionRepository->getByTransactionId(
                $this->transactionId,
                $this->payment->getId(),
                $this->order->getId()
            );
            if (!$transaction) {
                if ($this->transactionBuilderLoggingToggle->isTransactionBuilderLoggingEnabled()) {
                    $this->logger->info("Transaction Debug - trigger setTxnId method");
                }
                $transaction = $this->transactionRepository->create()->setTxnId($this->transactionId);
            }
            $transaction->setPaymentId($this->payment->getId())
                ->setPayment($this->payment)
                ->setOrderId($this->order->getId())
                ->setOrder($this->order)
                ->setTxnType($type)
                ->isFailsafe($this->failSafe);

            if ($this->payment->hasIsTransactionClosed()) {
                $transaction->setIsClosed((int)$this->payment->getIsTransactionClosed());
            }
            if ($this->transactionAdditionalInfo) {
                foreach ($this->transactionAdditionalInfo as $key => $value) {
                    $transaction->setAdditionalInformation($key, $value);
                }
            }
            $this->transactionAdditionalInfo = [];
            if ($this->transactionBuilderLoggingToggle->isTransactionBuilderLoggingEnabled()) {
                $this->logger->info("Transaction Debug - trigger setLastTransId and txn id = ".$transaction->getTxnId());
            }
            $this->payment->setLastTransId($transaction->getTxnId());
            $this->payment->setCreatedTransaction($transaction);
            $this->order->addRelatedObject($transaction);
            if ($this->document && $this->document instanceof AbstractModel) {
                $this->document->setTransactionId($transaction->getTxnId());
            }
            if ($this->transactionBuilderLoggingToggle->isTransactionBuilderLoggingEnabled()) {
                $this->logger->info("Transaction Debug - return TransactionInterface object");
            }   
            return $this->linkWithParentTransaction($transaction);
        }
        if ($this->transactionBuilderLoggingToggle->isTransactionBuilderLoggingEnabled()) {
            $this->logger->info("Transaction Debug - return null");
        }
        return null;
    }
}
