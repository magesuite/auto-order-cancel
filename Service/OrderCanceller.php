<?php

declare(strict_types=1);

namespace MageSuite\AutoOrderCancel\Service;

class OrderCanceller
{
    protected \MageSuite\AutoOrderCancel\Helper\Configuration $configuration;
    protected \Magento\Framework\DB\Transaction $dbTransaction;
    protected \Psr\Log\LoggerInterface $logger;
    protected \MageSuite\AutoOrderCancel\Model\ResourceModel\OrdersToCancelCollection $ordersToCancelCollection;
    protected \Magento\Sales\Api\OrderManagementInterface $orderManagement;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    public function __construct(
        \MageSuite\AutoOrderCancel\Helper\Configuration $configuration,
        \Magento\Framework\DB\Transaction $dbTransaction,
        \Psr\Log\LoggerInterface $logger,
        \MageSuite\AutoOrderCancel\Model\ResourceModel\OrdersToCancelCollection $ordersToCancelCollection,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->configuration = $configuration;
        $this->dbTransaction = $dbTransaction;
        $this->logger = $logger;
        $this->ordersToCancelCollection = $ordersToCancelCollection;
        $this->orderManagement = $orderManagement;
        $this->storeManager = $storeManager;
    }

    public function execute():void
    {
        foreach ($this->storeManager->getStores() as $store) {
            $storeId = (int) $store->getId();
            if ($this->configuration->getCancelOrdersEnabled($storeId) === false) {
                continue;
            }

            $this->cancelUnpaidOrders($storeId);
        }
    }

    public function cancelUnpaidOrders(?int $storeId = null):void
    {
        $orders = $this->ordersToCancelCollection->getOrders($storeId);

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
