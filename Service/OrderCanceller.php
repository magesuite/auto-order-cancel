<?php

declare(strict_types=1);

namespace MageSuite\AutoOrderCancel\Service;

class OrderCanceller
{
    protected \MageSuite\AutoOrderCancel\Helper\Configuration $configuration;
    protected \Magento\Framework\DB\Transaction $dbTransaction;
    protected \Psr\Log\LoggerInterface $logger;
    protected \MageSuite\AutoOrderCancel\Model\ResourceModel\OrdersToCancelCollection $ordersToCancelCollection;
    protected \MageSuite\OrderExport\Model\OrderRepositoryInterface $orderRepository;
    protected \Magento\Sales\Api\OrderManagementInterface $orderManagement;

    public function __construct(
        \MageSuite\AutoOrderCancel\Helper\Configuration $configuration,
        \Magento\Framework\DB\Transaction $dbTransaction,
        \Psr\Log\LoggerInterface $logger,
        \MageSuite\AutoOrderCancel\Model\ResourceModel\OrdersToCancelCollection $ordersToCancelCollection,
        \MageSuite\OrderExport\Model\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement
    ) {
        $this->configuration = $configuration;
        $this->dbTransaction = $dbTransaction;
        $this->logger = $logger;
        $this->ordersToCancelCollection = $ordersToCancelCollection;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function cancelUnpaidOrders():void
    {
        if ($this->configuration->getCancelOrdersEnabled() === false) {
            return;
        }

        $orders = $this->ordersToCancelCollection->getOrders();

        foreach ($orders as $order) {
            try {
                $this->cancelInvoices($order);
                $this->orderManagement->cancel($order->getEntityId());
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     * @throws \Exception
     */
    protected function cancelInvoices(\Magento\Sales\Model\Order $order):void
    {
        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoice->cancel();
            $this->dbTransaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            )->save();
        }
    }
}
