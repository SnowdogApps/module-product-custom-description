<?php

namespace Snowdog\CustomDescription\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('snowdog_custom_description'))
            ->addColumn(
                'entity_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                10,
                ['nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Description Title'
            )
            ->addColumn(
                'description',
                Table::TYPE_TEXT,
                '2M',
                ['nullable' => false],
                'Description'
            )->addColumn(
                'image',
                Table::TYPE_TEXT,
                '400',
                ['nullable' => false],
                'Image'
            )
            ->addColumn(
                'position',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Position'
            )
            ->setComment('Product Custom Description');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

}