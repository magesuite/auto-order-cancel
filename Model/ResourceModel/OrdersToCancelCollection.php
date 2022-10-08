<?php

declare(strict_types=1);

namespace MageSuite\AutoOrderCancel\Model\ResourceModel;

class OrdersToCancelCollection
{
    protected \MageSuite\AutoOrderCancel\Helper\Configuration $configuration;
    protected \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate;
    protected \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory;

    public function __construct(
        \MageSuite\AutoOrderCancel\Helper\Configuration $configuration,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory  $orderCollectionFactory
    ) {
        $this->configuration = $configuration;
        $this->localeDate = $localeDate;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    public function getOrders(?int $storeId = null):\Magento\Sales\Model\ResourceModel\Order\Collection
    {
        $cancelOrdersTimeInDays = $this->configuration->getCancelOrdersTime($storeId);
        $cancelOrdersDate = $this->localeDate->date()
            ->sub(new \DateInterval(sprintf('P%dD', $cancelOrdersTimeInDays)))
            ->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(
            \Magento\Sales\Api\Data\OrderInterface::STATE,
            \Magento\Sales\Model\Order::STATE_NEW
        );
        $orderCollection->addFieldToFilter(
            \Magento\Sales\Api\Data\OrderInterface::CREATED_AT,
            ['lt' => $cancelOrdersDate]
        );
        $orderCollectionSelect = $orderCollection->getSelect()
            ->join(
                ['sop' => 'sales_order_payment'],
                'main_table.entity_id = sop.parent_id',
                ['method']
            )
            ->where(
                'sop.method IN (?)',
                $this->configuration->getPaymentMethods($storeId)
            );

        if (!empty($storeId)) {
            $orderCollectionSelect->where(
                'main_table.store_id = ?',
                $storeId
            );
        }

        return $orderCollection;
    }
}
