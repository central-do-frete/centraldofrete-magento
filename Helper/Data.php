<?php

declare(strict_types=1);

namespace Buzz\CentralDoFrete\Helper;

use \Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 */
class Data
{

    const LBS_TO_KG_FACTOR = 0.453592;

    const KG_TO_LBS_FACTOR = 2.20462;

    private $scopeConfig;
    private $storeManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function isActive($store = null)
    {
        return (bool) $this->getCarrierConfig('active', $store);
    }

    public function getToken($store = null)
    {
        return $this->getCarrierConfig('token', $store);
    }

    public function getWeightAttribute($store = null)
    {
        return $this->getCarrierConfig('attributes_mapping/weight_attribute', $store);
    }

    public function getHeightAttribute($store = null)
    {
        return $this->getCarrierConfig('attributes_mapping/height_attribute', $store);
    }

    public function getLengthAttribute($store = null)
    {
        return $this->getCarrierConfig('attributes_mapping/length_attribute', $store);
    }

    public function getWidthAttribute($store = null)
    {
        return $this->getCarrierConfig('attributes_mapping/width_attribute', $store);
    }

    public function getDefaultWeight($store = null)
    {
        return (float) $this->getCarrierConfig('default_measurements/default_weight', $store);
    }

    public function getDefaultHeight($store = null)
    {
        return (float) $this->getCarrierConfig('default_measurements/default_height', $store);
    }

    public function getDefaultLength($store = null)
    {
        return (float) $this->getCarrierConfig('default_measurements/default_length', $store);
    }

    public function getDefaultWidth($store = null)
    {
        return (float) $this->getCarrierConfig('default_measurements/default_width', $store);
    }

    public function getAddDays($store = null)
    {
        return (int) $this->getCarrierConfig('adddays', $store);
    }

    public function isDebugModeEnabled($store = null)
    {
        return (bool) $this->getCarrierConfig('debug', $store);
    }

    public function getOriginPostcode($store = null)
    {
        return $this->get('shipping', 'origin', 'postcode', $store);
    }

    public function getCarrierConfig($field, $store = null)
    {
        return $this->get('carriers', \Buzz\CentralDoFrete\Model\Carrier\CentralDoFrete::CARRIER_CODE, $field, $store);
    }

    public function get($section, $group, $field, $store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        $path = implode('/', [$section, $group, $field]);
        return $this->scopeConfig->getValue($path, $scopeType, $this->getStore($store));
    }

    private function getStore($store = null)
    {
        try {
            return $this->storeManager->getStore($store);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            return $this->storeManager->getDefaultStoreView();
        }
    }

    private function getWeightUnit()
    {
        return $this->scopeConfig->getValue('general/locale/weight_unit');
    }

    public function convertToKg($weight)
    {
        switch ($this->getWeightUnit()) {
            case 'lbs':
                return $weight * self::LBS_TO_KG_FACTOR;
            case 'kgs':
            default:
                return $weight;
        }
    }

    public function convertToLbs($weight)
    {
        switch ($this->getWeightUnit()) {
            case 'kgs':
                return $weight * self::KG_TO_LBS_FACTOR;
            case 'lbs':
            default:
                return $weight;
        }
    }

}
