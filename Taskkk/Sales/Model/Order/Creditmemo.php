<?php
declare(strict_types=1);

namespace Base\Sales\Model\Order;

use Magento\Sales\Model\Order\CreditmemoRepository;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\CreditmemoSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Base\OrderStatusXml\Helper\Logger\Logger;
use Base\FeatureToggle\Api\Options\CreditmemoLogsInterface;

/**
 * This class is overridden to add debug logs for Creditmemo issue
 */
class Creditmemo extends CreditmemoRepository
{
    /**
     * @param Metadata $metadata
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param Logger $logger
     * @param CreditmemoLogsInterface $creditmemoLogsToggle
     */
    public function __construct(
        Metadata $metadata,
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor = null,
        private readonly Logger $logger,
        private readonly CreditmemoLogsInterface $creditmemoLogsToggle
    ) {
        parent::__construct(
            $metadata,
            $searchResultFactory,
            $collectionProcessor
        );
    }

    /**
     * Performs persist operations for a specified credit memo.
     * Logs added to debug the creditmemo issue.
     *
     * @throws CouldNotSaveException
     */
    public function save(CreditmemoInterface $entity): CreditmemoInterface
    {
        $this->logger->info("Creditmemo logs status : " . $this->creditmemoLogsToggle->isCreditmemoLogsEnabled());
        if (!$this->creditmemoLogsToggle->isCreditmemoLogsEnabled()) {
            return parent::save($entity);
        }

        try {
            $this->logger->info(sprintf(
                "Trigger creditmemo save method for order id : %s",
                $entity->getOrderId()
            ));
            $this->metadata->getMapper()->save($entity);
            $this->registry[$entity->getEntityId()] = $entity;
            $this->logger->info(sprintf(
                "Creditmemo saved successfully for order id : %s",
                $entity->getOrderId()
            ));
        } catch (LocalizedException $e) {
            $this->logger->info(sprintf(
                "LocalizedException in Creditmemo::save for order id %s and %s and trace %s ",
                $entity->getOrderId(),
                $e->getMessage(),
                $e->getTraceAsString()
            ));
            throw new CouldNotSaveException(__($e->getMessage()), $e);
        } catch (\Exception $e) {
            $this->logger->info(sprintf(
                "GeneralException in Creditmemo::save for order id %s and %s and trace %s ",
                $entity->getOrderId(),
                $e->getMessage(),
                $e->getTraceAsString()
            ));
            throw new CouldNotSaveException(__("The credit memo couldn't be saved."), $e);
        }
        return $this->registry[$entity->getEntityId()];
    }
}
