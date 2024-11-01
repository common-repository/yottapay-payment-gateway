<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay Loyalty Worker class
 */
class WC_YottaPay_Loyalty_Worker
{
    /**
     * Check order status and welcome loyalty options
     */
    public static function check_create_deferred_available($order)
    {
        // - Get plugin options
        $options = WC_YottaPay_Options::get_gateway_options();

        // - Verify loyalty_bonus
        if ($options['loyalty_bonus'] == 0)
        {
            throw new Exception('Welcome Loyalty Bonus is disabled in admin panel');
        }

        // - Verify order status
        $order_status = $order->get_status();
        if ($order_status != 'processing' && $order_status != 'completed')
        {
            throw new Exception('Invalid order status for process loyalty: ' . $order_status);
        }

        // - Compare order date_created and loyalty_start_period option
        if (strlen($options['loyalty_start_period']) != 0)
        {
            $dateOrderCreated = strtotime($order->get_date_created());
            $dateLoyaltyStart = strtotime($options['loyalty_start_period']);

            if ($dateOrderCreated < $dateLoyaltyStart)
            {
                throw new Exception('Too old order');
            }
        }
    }
}
