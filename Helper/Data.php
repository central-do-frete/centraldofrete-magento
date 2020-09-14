<?php

declare(strict_types=1);

namespace Buzz\CentralDoFrete\Helper;

use \Magento\Store\Model\ScopeInterface;
use GuzzleHttp\Psr7\ResponseFactory;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Magento\Framework\Webapi\Rest\Request;


/**
 * Class Config
 */
class Data
{

    const LBS_TO_KG_FACTOR = 0.453592;

    const KG_TO_LBS_FACTOR = 2.20462;

    const API_REQUEST_URI = 'https://api.centraldofrete.com/';
    const SB_API_REQUEST_URI = 'https://sandbox.centraldofrete.com/';

    private $scopeConfig;
    private $storeManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ResponseFactory $responseFactory,
        \Buzz\CentralDoFrete\Model\CargoTypesFactory $cargoTypesFactory,
        ClientFactory $clientFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->cargoTypesFactory = $cargoTypesFactory;
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

    public function getDefaultCargoType($store = null)
    {
        $cargoType = (int) $this->getCarrierConfig('default_cargo_type', $store);
        $cargoTypeObject = $this->cargoTypesFactory->create()->load($cargoType);
        return $cargoTypeObject->getData('id_api');
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

    public function doRequest(
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
    ) {
        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => self::API_REQUEST_URI
        ]]);

        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );
        } catch (GuzzleException $exception) {
            /** @var Response $response */
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }

    public function getAPIBaseURL()
    {
        if ($this->getCarrierConfig('sandbox')) {
            return self::SB_API_REQUEST_URI;
        } else {
            return self::API_REQUEST_URI;
        }
    }
}
