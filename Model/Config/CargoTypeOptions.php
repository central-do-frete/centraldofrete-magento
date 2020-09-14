<?php

namespace Buzz\CentralDoFrete\Model\Config;

class CargoTypeOptions extends \Magento\Eav\Model\Entity\Attribute\Source\Config
{
    protected $productCollection;

    public function __construct(
        \Buzz\CentralDoFrete\Model\ResourceModel\CargoTypes\Collection $cargoTypesCollection
    ) {
        $this->cargoTypesCollection = $cargoTypesCollection;
    }

    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        if (sizeof($options) > 0) {
            foreach ($options as $option) {
                if (isset($option['value']) && $option['value'] == $value) {
                    return __($option['label']);
                }
            }
        }
        if (isset($options[$value])) {
            return $option[$value];
        }
        return false;
    }

    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    public function toOptionArray()
    {
        // Load the cargoTypes as options
        $cargoTypes = $this->cargoTypesCollection->load();
        $options = [];
        /* @todo: add query to load selected options */
        $options[] = [
            "value" => null,
            "label" => "Selecione um tipo de carga"
        ];
        foreach ($cargoTypes as $cargo) {
            $options[] = [
                "value" => $cargo->getData('id'),
                "label" => $cargo->getData('name')
            ];
        }
        return $options;
    }
}
