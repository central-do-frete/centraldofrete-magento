<?php
namespace Buzz\CentralDoFrete\Model\ResourceModel\CargoTypes;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'centraldofrete_cargotypes_id';
    protected $_eventPrefix = 'centraldofrete_cargotypes_collection_prefix';
    protected $_eventObject = 'centraldofrete_cargotypes_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Buzz\CentralDoFrete\Model\CargoTypes', 'Buzz\CentralDoFrete\Model\ResourceModel\CargoTypes');
    }

}