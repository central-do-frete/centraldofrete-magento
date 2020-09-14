<?php

namespace Buzz\CentralDoFrete\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $productTypes = [
            Type::TYPE_SIMPLE,
            Type::TYPE_VIRTUAL,
        ];
        $productTypes = join(',', $productTypes);

        $sourceCargotypes = 'Buzz\CentralDoFrete\Model\Config\CargoTypeOptions';
        $eavSetup->addAttribute(
            Product::ENTITY,
            'centraldofrete_cargotype',
            [
                'type'                    => 'int',
                'label'                   => 'Cargo Type',
                'input'                   => 'select',
                'sort_order'              => 50,
                'global'                  => Attribute::SCOPE_WEBSITE,
                'user_defined'            => true,
                'required'                => false,
                'used_in_product_listing' => false,
                'apply_to'                => $productTypes,
                'group'                   => 'General',
                'unique'                  => false,
                'visible_on_front'        => false,
                'searchable'              => false,
                'filterable'              => false,
                'comparable'              => false,
                'visible'                 => false,
                'backend'                 => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'frontend'                => '',
                'class'                   => '',
                'source'                  => $sourceCargotypes,
                'default'                 => '',
            ]
        );

        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable('centraldofrete_cargotypes');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'id_api',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'default' => 0],
                    'Cargo Type ID'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Name'
                )
                ->addColumn(
                    'cargo_type_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'Parent Cargo Type ID'
                )
                ->setComment('Tabela para armazenamento de informações do tipo de carga disponibilizados pela API da Central do Frete.')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
    }
}
