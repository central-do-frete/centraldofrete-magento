<?php

namespace Buzz\CentralDoFrete\Observer;

use \Magento\Sales\Model\OrderFactory;
use \Magento\Quote\Model\QuoteFactory;


class SaveQuotationCode implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param OrderFactory $orderFactory
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        OrderFactory $orderFactory,
        QuoteFactory $quoteFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderId = (int) $observer->getData('order_ids')[0];
        $order = $this->orderFactory->create()->load($orderId);
        $quote = $this->quoteFactory->create()->load($order->getQuoteId());

        $code = $quote->getCentraldofreteCode();
        $order->setCentraldofreteCode($code);
        $order->save();
    }
}
