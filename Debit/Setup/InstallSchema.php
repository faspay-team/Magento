<?php


namespace Faspay\Debit\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableChannel = $installer->getConnection()
            ->newTable($installer->getTable('faspay_channel'))
            ->addColumn(
                'bank_num',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'primary' => true],
                'Bank Channel Number'
            )
            ->addColumn(
                'bank_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Bank Name'
            )
            ->addColumn(
                'bank_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Type of payment'

            )
            ->addColumn(
                'bank_flow',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Type of payment'

            )
            ->setComment("Bank Channel DB");

        $tableOrder = $installer->getConnection()
            ->newTable($installer->getTable('faspay_order'))
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false,'primary' => true],
                'Order ID in merchant'

            )
            ->addColumn(
                'trx_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'TRX ID from faspay'

            )
            ->addColumn(
                'bank_num',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Bank Channel Number'

            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'

            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'

            )
            ->addColumn(
                'payment_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => "PENDING"],
                'Status Now'

            )
            ->addColumn(
                'signature',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Signature'

            )
            ->addColumn(
                'reserve1',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                512,
                ['nullable' => false],
                'Signature'

            )
            ->addColumn(
                'reserve2',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                512,
                ['nullable' => false],
                'Signature'

            )->setComment("Order ID and TRX ID");

        $installer->getConnection()->createTable($tableOrder);
        $installer->getConnection()->createTable($tableChannel);

        $installer->endSetup();
    }
}