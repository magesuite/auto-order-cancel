<?php

declare(strict_types=1);

namespace MageSuite\AutoOrderCancel\Helper;

class Configuration extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const XML_PATH_CANCEL_ORDERS_TIME = 'payment/banktransfer/cancel_orders_time';
    public const XML_PATH_CANCEL_ORDERS_ENABLED = 'payment/banktransfer/cancel_orders_enabled';

    /**
     * @return int
     */
    public function getCancelOrdersTime(): int
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_CANCEL_ORDERS_TIME);
    }

    /**
     * @return bool
     */
    public function getCancelOrdersEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_CANCEL_ORDERS_ENABLED);
    }
}
