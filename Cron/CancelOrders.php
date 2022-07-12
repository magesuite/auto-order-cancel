<?php

declare(strict_types=1);

namespace MageSuite\AutoOrderCancel\Cron;

class CancelOrders
{
    protected \MageSuite\AutoOrderCancel\Service\OrderCancellerFactory $orderCancellerFactory;

    public function __construct(
        \MageSuite\AutoOrderCancel\Service\OrderCancellerFactory $orderCancellerFactory
    ) {
        $this->orderCancellerFactory = $orderCancellerFactory;
    }

    public function execute()
    {
        /** @var \MageSuite\AutoOrderCancel\Service\OrderCanceller $orderCanceller */
        $orderCanceller = $this->orderCancellerFactory->create();
        $orderCanceller->cancelUnpaidOrders();
    }
}
