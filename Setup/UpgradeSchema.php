<?php

namespace Buzz\CentralDoFrete\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

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

        $quote = 'quote';
        $orderTable = 'sales_order';

        //Quote table
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quote),
                'centraldofrete_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Central do Frete Quote Code'
                ]
            );
        //Order table
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'centraldofrete_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Central do Frete Quote Code'
                ]
            );

        $setup->endSetup();
    }
}
