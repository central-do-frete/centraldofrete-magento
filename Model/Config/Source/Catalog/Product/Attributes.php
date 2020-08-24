<?php

namespace Buzz\CentralDoFrete\Model\Config\Source\Catalog\Product;

class Attributes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array
     */
    private $_options = [];

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $_attributeRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    public function __construct(
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->_attributeRepository = $attributeRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function toOptionArray()
    {
        $options = [];

        foreach ($this->toArray() as $code => $label) {
            $options[] = [
                'label' => "{$label} [{$code}]",
                'value' => $code,
            ];
        }

        return $options;
    }

    public function toArray()
    {
        if (empty($this->_options)) {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            foreach ($this->getCollection() as $attribute) {
                $this->_options[$attribute->getAttributeCode()] = $attribute->getDefaultFrontendLabel();
            }
        }

        return $this->_options;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    private function getCollection()
    {
        $searchCriteria = $this->_searchCriteriaBuilder->create();
        $searchCriteria->setData('entity_type_id', \Magento\Catalog\Model\Product::ENTITY);

        /** @var \Magento\Catalog\Api\Data\ProductAttributeSearchResultsInterface $attributeRepository */
        $attributeRepository = $this->_attributeRepository->getList(
            $searchCriteria
        );

        return $attributeRepository->getItems();
    }
}
