<?php

declare(strict_types=1);

namespace MageSuite\AutoOrderCancel\Helper;

class Configuration extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const XML_PATH_CANCEL_ORDERS_TIME = 'auto_order_cancel/configuration/cancel_orders_time';
    public const XML_PATH_CANCEL_ORDERS_ENABLED = 'auto_order_cancel/configuration/cancel_orders_enabled';
    public const XML_PATH_CANCEL_ORDERS_PAYMENT_METHODS = 'auto_order_cancel/configuration/payment_methods';

    public function getCancelOrdersTime(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_CANCEL_ORDERS_TIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCancelOrdersEnabled(?int $storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_CANCEL_ORDERS_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getPaymentMethods(?int $storeId = null): array
    {
        return explode(
            ',',
            (string) $this->scopeConfig->getValue(
                self::XML_PATH_CANCEL_ORDERS_PAYMENT_METHODS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }
}
