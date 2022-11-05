<?php

declare(strict_types=1);

namespace MageSuite\AutoOrderCancel\Test\Integration\Service;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class OrderCancellerTest extends \PHPUnit\Framework\TestCase
{
    const TEST_ORDER_INCREMENT_ID = '100000001';

    protected ?\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate;
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\MageSuite\AutoOrderCancel\Service\OrderCanceller $orderCanceller;
    protected ?\Magento\Sales\Api\OrderRepositoryInterface $orderRepository;
    protected ?\Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->localeDate = $this->objectManager->get(
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class
        );
        $this->orderRepository = $this->objectManager->create(
            \Magento\Sales\Api\OrderRepositoryInterface::class
        );
        $this->orderCanceller = $this->objectManager->get(
            \MageSuite\AutoOrderCancel\Service\OrderCanceller::class
        );
        $this->searchCriteriaBuilder = $this->objectManager->get(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
    }

    /**
     * @magentoConfigFixture payment/banktransfer/active 1
     * @magentoConfigFixture current_store auto_order_cancel/configuration/time 1
     * @magentoConfigFixture current_store auto_order_cancel/configuration/enabled 1
     * @magentoConfigFixture current_store auto_order_cancel/configuration/payment_methods banktransfer
     * @magentoDataFixture MageSuite_AutoOrderCancel::Test/Integration/_files/order_with_1_qty_product.php
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testCancelOrder(): void
    {
        $order = $this->getOrder(self::TEST_ORDER_INCREMENT_ID);
        $this->assertEquals(\Magento\Sales\Model\Order::STATE_NEW, $order->getState());
        $cancelOrdersDate = $this->localeDate->date()
            ->sub(new \DateInterval('P2D'))
            ->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $order->setCreatedAt($cancelOrdersDate);
        $this->orderRepository->save($order);

        $this->orderCanceller->execute();

        $order = $this->getOrder(self::TEST_ORDER_INCREMENT_ID);
        $this->assertEquals(\Magento\Sales\Model\Order::STATE_CANCELED, $order->getState());
    }

    /**
     * @magentoConfigFixture current_store payment/banktransfer/active 1
     * @magentoConfigFixture current_store auto_order_cancel/configuration/time 5
     * @magentoConfigFixture current_store auto_order_cancel/configuration/enabled 1
     * @magentoConfigFixture current_store auto_order_cancel/configuration/payment_methods banktransfer
     * @magentoDataFixture MageSuite_AutoOrderCancel::Test/Integration/_files/order_with_1_qty_product.php
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testNotCancelOrder(): void
    {
        $order = $this->getOrder(self::TEST_ORDER_INCREMENT_ID);
        $this->assertEquals(\Magento\Sales\Model\Order::STATE_NEW, $order->getState());
        $cancelOrdersDate = $this->localeDate->date()
            ->sub(new \DateInterval('P2D'))
            ->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $order->setCreatedAt($cancelOrdersDate);
        $this->orderRepository->save($order);

        $this->orderCanceller->cancelUnpaidOrders();

        $order = $this->getOrder(self::TEST_ORDER_INCREMENT_ID);
        $this->assertEquals(\Magento\Sales\Model\Order::STATE_NEW, $order->getState());
    }

    /**
     * @magentoConfigFixture payment/banktransfer/active 1
     * @magentoConfigFixture current_store auto_order_cancel/configuration/time 1
     * @magentoConfigFixture current_store auto_order_cancel/configuration/enabled 1
     * @magentoDataFixture MageSuite_AutoOrderCancel::Test/Integration/_files/order_with_1_qty_product.php
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testNotCancelOrderNoPaymentSelected(): void
    {
        $order = $this->getOrder(self::TEST_ORDER_INCREMENT_ID);
        $this->assertEquals(\Magento\Sales\Model\Order::STATE_NEW, $order->getState());
        $cancelOrdersDate = $this->localeDate->date()
            ->sub(new \DateInterval('P2D'))
            ->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $order->setCreatedAt($cancelOrdersDate);
        $this->orderRepository->save($order);

        $this->orderCanceller->execute();

        $order = $this->getOrder(self::TEST_ORDER_INCREMENT_ID);
        $this->assertEquals(\Magento\Sales\Model\Order::STATE_NEW, $order->getState());
    }

    protected function getOrder(string $orderIncrementId): \Magento\Sales\Api\Data\OrderInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $orderIncrementId)
            ->create();
        $orders = $this->orderRepository->getList($searchCriteria);
        $orders = $orders->getItems();

        return array_shift($orders);
    }
}
