<?php

namespace Buzz\CentralDoFrete\Observer;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Buzz\CentralDoFrete\Helper\Data as HelperData;

class GetCargoTypes implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var EventManager
     */
    private $eventManager;
    private $helper;
    protected $logger;
    protected $cargoTypeFactory;

    /*
   * @param \Magento\Framework\Event\ManagerInterface as EventManager
   */
    public function __construct(
        EventManager $eventManager,
        HelperData $helper,
        \Buzz\CentralDoFrete\Model\CargoTypesFactory $cargoTypeFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->cargoTypeFactory = $cargoTypeFactory;
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (strlen($this->helper->getToken()) > 0) {
            $getCargoTypeEndpoint = 'v1/cargo-type';
            $headers['headers'] = [
                'Authorization' => $this->helper->getToken(),
                'Content-Type'     => 'application/json',
            ];

            $this->logger->debug('Central do Frete:: URL');
            $this->logger->debug($this->helper->getAPIBaseURL());

            $this->logger->debug('Central do Frete:: Endpoint');
            $this->logger->debug($getCargoTypeEndpoint);

            $this->logger->debug('Central do Frete:: Headers');
            $this->logger->debug(json_encode($headers));
            $response = $this->helper->doRequest(
                $this->helper->getAPIBaseURL() . $getCargoTypeEndpoint,
                $headers,
                'GET'
            );

            if ($response->getStatusCode() != 200) {
                $this->logger->error('Central do Frete:: HTTP Error ' . $response->getStatusCode());
                $this->logger->error('Central do Frete:: ' . $response->getReasonPhrase());
                return false;
            }

            $body = $response->getBody();
            $cargoTypes = json_decode($body->getContents());

            foreach ($cargoTypes as $cargoType) {
                $cargoTypeObject = $this->cargoTypeFactory->create();
                $cargoTypeById = $cargoTypeObject->getCollection()->addFieldToFilter('id_api', $cargoType->id);
                if (count($cargoTypeById->getData()) == 0) {
                    $cargoTypeObject->setIdApi($cargoType->id);
                    $cargoTypeObject->setName($cargoType->name);
                    $cargoTypeObject->setCargoTypeId($cargoType->cargo_type_id);
                    try {
                        $cargoTypeObject->save();
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        echo $e->getMessage();
                    }
                }
            }
            echo "passamos portudo!";
        }
        return $this;
    }
}
