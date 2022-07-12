<?php

\Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance()
    ->requireDataFixture('Magento/Sales/_files/order_with_1_qty_product.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->get(\Magento\Sales\Api\Data\OrderInterfaceFactory::class)->create()
    ->loadByIncrementId('100000001');

$payment = $objectManager->create(\Magento\Sales\Model\Order\Payment::class);
$payment->setMethod(\Magento\OfflinePayments\Model\Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE);
$order->setPayment($payment);
$orderRepository = $objectManager->create(\Magento\Sales\Api\OrderRepositoryInterface::class);
$orderRepository->save($order);
