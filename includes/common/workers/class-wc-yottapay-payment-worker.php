<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay Payment Worker class
 */
class WC_YottaPay_Payment_Worker
{
    /**
     * Validate order currency
     */
    public static function validate_order_currency($order)
    {
        if ($order->get_currency() != 'GBP')
        {
            throw new WC_YottaPay_Exception('The payment method currently only accepts transactions in GBP');
        }
    }

    /**
     * Validate billing country
     */
    public static function validate_billing_country($order)
    {
        if ($order->get_billing_country() != 'GB')
        {
            throw new WC_YottaPay_Exception('The payment method is currently only available in the UK');
        }
    }

    /**
     * Validate that the order meets the minimum order amount
     */
    public static function validate_minimum_order_amount($order, $is_sc = false)
    {
        // - Check for payment initialization source (supercheckout or order pay or checkout)
        if ($is_sc)
        {
		    $order_total = $order->get_total();
        }
        elseif (isset($_GET['pay_for_order']))
        {
            $query_parameter_key = isset($_GET['key']) ? wc_clean(wp_unslash($_GET['key'])) : '';

            if ($query_parameter_key == '')
            {
                throw new Exception('Invalid order payment page');
            }
            
            $order_total = $order->get_total();
        }
        else
        {
		    $order_total = WC()->cart->get_total(false);
        }

        // - Check order total
        if (number_format(round($order_total, 2, PHP_ROUND_HALF_UP), 2, '.', '') < 0.01)
        {
            throw new WC_YottaPay_Exception('Minimum allowed order total is 0.01GBP to use the payment method');
        }
    }

    /**
     * Set gateway discount to order and return discounted total
     */
    public static function set_gateway_discount_to_order($order, $order_total)
    {
        // - Init
        $formatted_discounted_total = 0;

        // - Get plugin options
        $options = WC_YottaPay_Options::get_gateway_options();

        // - Get gateway discount option value
        $gateway_discount = $options['gateway_discount'];

        // - Check gateway discount
        if ($gateway_discount > 0)
        {
            // - Calculate gateway-discounted order total
            $discount_amount = $order_total * ($gateway_discount / 100);
            $discounted_total = (float)($order_total - $discount_amount);
            $formatted_discounted_total = (float)(number_format(round($discounted_total, 2, PHP_ROUND_HALF_UP), 2, '.', ''));

            // - Check minimum allowed
            if ($formatted_discounted_total < 0.01)
            {
                $formatted_discounted_total = 0.01;
            }
        }

        // - Add discount percentage value to order meta (anyway)
        $order->update_meta_data('yottapay_gateway_discount_percentage', $gateway_discount);
        $order->save();

        // - Return total
        if ($formatted_discounted_total > 0)
        {
            return $formatted_discounted_total;
        }
        else
        {
            return $order_total;
        }
    }

    /**
     * Return other_request_params array containing Sourcebuster cookies
     */
    public static function get_other_request_params()
    {
        try
        {
            $sb_params = [];
            $sb_cookie_value = $_COOKIE['sbjs_first'];

            if (isset($sb_cookie_value) && $sb_cookie_value != '')
            {
                $splited_sb_cookie_value = explode('|||', $sb_cookie_value);
                foreach ($splited_sb_cookie_value as &$value)
                {
                    $splited_value = explode('=', $value);
                    $sb_params[$splited_value[0]] = $splited_value[1];
                }
                
                $other_request_params = [
                    'utm_source' => $sb_params['src'],
                    'utm_medium' => $sb_params['mdm'],
                    'utm_campaign' => $sb_params['cmp'],
                    'utm_content' => $sb_params['cnt'],
                    'utm_term' => $sb_params['trm'],
                ];

                return $other_request_params;
            }
            else
            {
                throw new Exception('No Sourcebuster cookies');
            }
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_Payment_Worker', 'get_other_request_params', $e->getMessage());            

            $default_other_request_params = [
                'utm_source' => '',
                'utm_medium' => '',
                'utm_campaign' => '',
                'utm_content' => '',
                'utm_term' => '',
            ];

            return $default_other_request_params;
        }
    }
}
