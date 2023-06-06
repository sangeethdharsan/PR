<?php
declare(strict_types=1);

namespace Base\Sales\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    final const SALES_INVOICE_TABLE = 'sales_invoice';
    final const SALES_CREDITMEMO_TABLE = 'sales_creditmemo';
    final const TRANSACTION_ID = 'transaction_id';

    /**
     * Upgrades DB schema for a module.
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * 
     * @return void
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.0.1', '<')) {
            $this->addIndexToSalesInvoiceTable($setup);
            $this->addIndexToSalesCreditMemoTable($setup);
        }

        $setup->endSetup();
    }

    /**
     * Add Index to transaction_id in sales_invoice table.
     *
     * @return void
     */
    private function addIndexToSalesInvoiceTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addIndex(
            $setup->getTable(static::SALES_INVOICE_TABLE),
            $setup->getIdxName(
                static::SALES_INVOICE_TABLE,
                [static::TRANSACTION_ID]
            ),
            [static::TRANSACTION_ID]
        );
    }

    /**
     * Add Index to transaction_id in sales_creditmemo table.
     *
     * @return void
     */
    private function addIndexToSalesCreditMemoTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addIndex(
            $setup->getTable(static::SALES_CREDITMEMO_TABLE),
            $setup->getIdxName(
                static::SALES_CREDITMEMO_TABLE,
                [static::TRANSACTION_ID]
            ),
            [static::TRANSACTION_ID]
        );
    }
}
