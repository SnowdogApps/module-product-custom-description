<?php

namespace Snowdog\CustomDescription\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class InstallSchema
 * @package Snowdog\CustomDescription\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.2.0', '<')) {
            $this->addProductEntityForeignKey($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addProductEntityForeignKey(SchemaSetupInterface $setup)
    {
        $fkName = $setup->getFkName(
            'snowdog_custom_description',
            'product_id',
            'catalog_product_entity',
            'entity_id'
        );
        $tableName = $setup->getTable('snowdog_custom_description');
        $connection = $setup->getConnection();
        $connection->changeColumn(
            $tableName,
            'product_id',
            'product_id',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => false,
                'unsigned' => true,
                'length' => 10
            ]
        );
        $connection->addForeignKey(
            $fkName,
            $tableName,
            'product_id',
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        );
    }
}
