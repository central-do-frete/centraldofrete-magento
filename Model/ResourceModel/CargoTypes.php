<?php

namespace Buzz\CentralDoFrete\Model\ResourceModel;


class CargoTypes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('centraldofrete_cargotypes', 'id');
    }
}
