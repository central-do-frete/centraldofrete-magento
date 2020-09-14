<?php

namespace Buzz\CentralDoFrete\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\Product\Type as ProductType;

class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $sourceCargotypes = 'Buzz\CentralDoFrete\Model\Config\CargoTypeOptions';
        $productTypes = [
            ProductType::TYPE_SIMPLE,
            ProductType::TYPE_VIRTUAL,
        ];
        $productTypes = join(',', $productTypes);
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'centraldofrete_cargotype',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Central do Frete Cargo Type',
                'input' => 'select',
                'class' => '',
                'group' => 'General',
                'source' => $sourceCargotypes,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => $productTypes
            ]
        );

    }
}
