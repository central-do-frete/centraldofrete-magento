<?php

namespace Buzz\CentralDoFrete\Model;

class CargoTypes extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'centraldofrete_cargotypes';
    protected $_cacheTag = 'centraldofrete_cargotypes';
    protected $_eventPrefix = 'centraldofrete_cargotypes';

    protected function _construct()
    {
        $this->_init('Buzz\CentralDoFrete\Model\ResourceModel\CargoTypes');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
