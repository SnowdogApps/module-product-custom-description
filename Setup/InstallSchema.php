<?php

namespace Snowdog\CustomDescription\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 * @package Snowdog\CustomDescription\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $this->createSnowdogCustomDescriptionTable($installer);
        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function createSnowdogCustomDescriptionTable(SchemaSetupInterface $installer)
    {
        $fkName = $installer->getFkName(
            'snowdog_custom_description',
            'product_id',
            'catalog_product_entity',
            'entity_id'
        );
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
                ['nullable' => false, 'unsigned' => true],
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
            ->addForeignKey(
                $fkName,
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Product Custom Description');

        $installer->getConnection()->createTable($table);
    }
}
