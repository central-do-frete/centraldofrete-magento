<?php

declare(strict_types=1);

namespace Buzz\CentralDoFrete\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

use Buzz\CentralDoFrete\Helper\Data as HelperData;

class CentralDoFrete extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{

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
        \Magento\Catalog\Model\ProductFactory $productFactory,
        HelperData $helper,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_helper = $helper;
        $this->_productFactory = $productFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->_helper->isActive()) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        $quotationData = [];
        $quotationData['cargo_types'] = [];

        $this->_logger->debug('Central do Frete:: coletando dados dos itens');
        $total = 0;
        foreach ($request->getAllItems() as $item) {
            
            if (!$total) {
                $total = $item->getQuote()->collectTotals()->getGrandTotal();
            }

            $productId = $item->getProduct()->getId();
            if ($option = $item->getOptionByCode('simple_product')) {
                $productId = $option->getProduct()->getId();
            }
            $productInstance = $this->_productFactory->create()->load($productId);

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
            $productCargoType = $productInstance->getData('centraldofrete_cargotype');
            if ($productCargoType) {
                $this->_logger->debug('Central do Frete:: cargo type do produto');
                $lastCargoType = $productCargoType;
            } else {
                $this->_logger->debug('Central do Frete:: cargo type padrão');
                $lastCargoType = $this->_helper->getDefaultCargoType();
            }
            $quotationData['cargo_types'][] = $lastCargoType;
            $this->_logger->debug('Central do Frete:: cargo type ' . $lastCargoType . ' ' . $productInstance->getName());
        }
        $quotationData['cargo_types'] = array_unique($quotationData['cargo_types']);
        $quotationData['invoice_amount'] = $total;

        $originPostcode = $this->_scopeConfig->getValue(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_POSTCODE);
        $originPostcode = preg_replace('/[^0-9]/', null, $originPostcode);
        $originPostcode = str_pad($originPostcode, 8, '0', STR_PAD_LEFT);
        $quotationData['from'] = preg_replace('/[^0-9]/', '', $originPostcode);

        $destinationPostcode = $request->getDestPostcode();
        $destinationPostcode = preg_replace('/[^0-9]/', null, $destinationPostcode);
        $destinationPostcode = str_pad($destinationPostcode, 8, '0', STR_PAD_LEFT);
        $quotationData['to'] = preg_replace('/[^0-9]/', '', $destinationPostcode);

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
        $this->_logger->debug($this->_helper->getAPIBaseURL());

        $this->_logger->debug('Central do Frete:: Endpoint');
        $this->_logger->debug($newQuotationEndpoint);

        $this->_logger->debug('Central do Frete:: Headers');
        $this->_logger->debug(json_encode($headers));
        $response = $this->_helper->doRequest(
            $this->_helper->getAPIBaseURL() . $newQuotationEndpoint,
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
        $response = $this->_helper->doRequest(
            $detailsEndpoint,
            $headers
        );

        $this->_logger->debug('Central do Frete:: Endpoint');
        $this->_logger->debug($detailsEndpoint);

        if ($response->getStatusCode() != 200) {
            $this->_logger->error('Central do Frete:: Erro ao obter cotações - ' . $response->getStatusCode());
            return false;
        }
        $quote->setData('centraldofrete_code', $code);
        $quote->save();

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
}
