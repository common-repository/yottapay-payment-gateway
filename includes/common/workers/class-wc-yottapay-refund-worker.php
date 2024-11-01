<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay Refund Worker class
 */
class WC_YottaPay_Refund_Worker
{
    /**
     * Yotta Pay refund form endpoint
     */
    public static function add_myaccount_refund_endpoint()
    {
        add_rewrite_endpoint('yottapay-refund-request-form', EP_ROOT | EP_PAGES);
        flush_rewrite_rules();
    }
}
