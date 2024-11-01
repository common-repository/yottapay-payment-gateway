<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay API Webhooks class
 */
class WC_YottaPay_API_Webhooks
{
    const AUTH_NEW = '/wc-api/yottapay_authorize_webhook';
    const ORDER_NEW = '/wc-api/yottapay_payment_webhook';
    const ORDER_CHECK_TO_CANCELLATION = '/wc-api/yottapay_payment_cancellation_webhook';
    const REFUND_NEW = '/wc-api/yottapay_refund_webhook';
}
