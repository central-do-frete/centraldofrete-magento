<?php

declare(strict_types=1);

namespace Buzz\CentralDoFrete\Model\Carrier;

use Exception;
use Magento\Quote\Model\Quote\Address\RateRequest;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use Buzz\CentralDoFrete\Helper\Data as HelperData;

class CentralDoFrete extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{

    const API_REQUEST_URI = 'https://api.centraldofrete.com/';
    const SB_API_REQUEST_URI = 'https://sandbox.centraldofrete.com/';
    const CARRIER_CODE = 'centraldofrete';

    /**
     * @var string
     */
    protected $_code = self::CARRIER_CODE;

    protected $_logger;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;


    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Checkout\Model\Cart $cart,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        HelperData $helper,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_cart = $cart;
        $this->_logger = $logger;
        $this->_clientFactory = $clientFactory;
        $this->_responseFactory = $responseFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_helper = $helper;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        $quote = $this->_cart->getQuote();


        $quotationData = [];
        $this->_logger->debug('Central do Frete:: coletando dados dos itens');
        foreach ($quote->getAllVisibleItems() as $item) {

            $defWidthAttribute = $this->_helper->getWidthAttribute();
            $defHeightAttribute = $this->_helper->getHeightAttribute();
            $defLengthAttribute = $this->_helper->getLengthAttribute();
            $defWeightAttribute = $this->_helper->getWeightAttribute();

            $valueHeight = $item->getProduct()->getData($defHeightAttribute);
            $valueWidth = $item->getProduct()->getData($defWidthAttribute);
            $valueLength = $item->getProduct()->getData($defLengthAttribute);
            $weight = $item->getProduct()->getData($defWeightAttribute);
            $valueWeight = $this->_helper->convertToKg($weight);

            if ($valueHeight == 0) {
                $valueHeight = $this->_helper->getDefaultHeight();
            }

            if ($valueWidth == 0) {
                $valueWidth = $this->_helper->getDefaultWidth();
            }

            if ($valueLength == 0) {
                $valueLength = $this->_helper->getDefaultLength();
            }

            if ($valueWeight == 0) {
                $valueWeight = $this->_helper->getDefaultWeight();
            }

            $quotationData['volumes'][] = [
                "quantity" => $item->getQty(),
                "width" => $valueWidth,
                "height" => $valueHeight,
                "length" => $valueLength,
                "weight" => $valueWeight
            ];
        }
        $quotationData['invoice_amount'] = $quote->getGrandTotal();

        $originPostcode = $this->_scopeConfig->getValue(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_POSTCODE);
        $originPostcode = preg_replace('/[^0-9]/', null, $originPostcode);
        $originPostcode = str_pad($originPostcode, 8, '0', STR_PAD_LEFT);
        $quotationData['from'] = preg_replace('/[^0-9]/', '', $originPostcode);

        $destinationPostcode = $request->getDestPostcode();
        $destinationPostcode = preg_replace('/[^0-9]/', null, $destinationPostcode);
        $destinationPostcode = str_pad($destinationPostcode, 8, '0', STR_PAD_LEFT);
        $quotationData['to'] = preg_replace('/[^0-9]/', '', $destinationPostcode);

        // $quotationData['from'] = "09531190";
        // $quotationData['to'] = "30240440";


        $quotationData['cargo_types'] = ['137'];
        $quotationData['recipient'] = [
            'document' => preg_replace('/[^0-9]/', '', '20893627000126'),
            'name' => 'Buzz e-Commerce'
        ];

        $this->_logger->debug('Central do Frete:: dados da requisição');
        $this->_logger->debug(json_encode($quotationData));

        $newQuotationEndpoint = 'v1/quotation';

        $headers['headers'] = [
            'Authorization' => $this->_helper->getToken(),
            'Content-Type'     => 'application/json',
        ];

        $headers['body'] = json_encode($quotationData);

        $this->_logger->debug('Central do Frete:: URL');
        $this->_logger->debug($this->getAPIBaseURL());

        $this->_logger->debug('Central do Frete:: Endpoint');
        $this->_logger->debug($newQuotationEndpoint);

        $this->_logger->debug('Central do Frete:: Headers');
        $this->_logger->debug(json_encode($headers));
        $response = $this->doRequest(
            $this->getAPIBaseURL() . $newQuotationEndpoint,
            $headers,
            'POST'
        );
        unset($headers['body']);

        if ($response->getStatusCode() != 200) {
            $this->_logger->error('Central do Frete:: HTTP Error ' . $response->getStatusCode());
            $this->_logger->error('Central do Frete:: ' . $response->getReasonPhrase());
            return false;
        }

        $body = $response->getBody();
        $code = json_decode($body->getContents());
        $code = $code->code;
        $this->_logger->debug('Central do Frete:: Código da cotação - ' . $code);

        $detailsEndpoint = "v1/quotation/" . $code;
        $response = $this->doRequest(
            $detailsEndpoint,
            $headers
        );

        $this->_logger->debug('Central do Frete:: Endpoint');
        $this->_logger->debug($detailsEndpoint);

        if ($response->getStatusCode() != 200) {
            $this->_logger->error('Central do Frete:: Erro ao obter cotações - ' . $response->getStatusCode());
            return false;
        }
        $body = $response->getBody();
        $responseRates = json_decode($body->getContents());
        $this->_logger->debug('Central do Frete:: Cotações Obtidas');
        $this->_logger->debug(json_encode($responseRates));

        foreach ($responseRates->prices as $price) {
            $method = $this->_rateMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($price->shipping_carrier . " - Até " . ($price->delivery_time + $this->_helper->getAddDays() . " dias para a entrega."));
            $method->setMethod($price->shipping_carrier);
            $method->setMethodTitle($this->_helper->getCarrierConfig('name'));
            $method->setPrice($price->price);
            $method->setCost($price->price);
            $method->setMethodDetails('DETALHES');
            $method->setMethodDescription('DESCRIPTION');
            $result->append($method);
        }

        $this->_logger->debug('Central do Frete:: Final da execução das cotações');

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {

        return [$this->_code => $this->_helper->getCarrierConfig('name')];
    }

    private function doRequest(
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
    ) {
        /** @var Client $client */
        $client = $this->_clientFactory->create(['config' => [
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
            $response = $this->_responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }

    public function getAPIBaseURL()
    {
        if ($this->getConfigFlag('sandbox')) {
            return self::SB_API_REQUEST_URI;
        } else {
            return self::API_REQUEST_URI;
        }
    }
}
