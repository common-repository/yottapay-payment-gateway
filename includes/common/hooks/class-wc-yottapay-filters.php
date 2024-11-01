<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay Filter Manager class
 */
class WC_YottaPay_Filters
{
    /**
     * Add filters
     */
    public static function add_filters()
    {
        // - Scheduler
        add_filter('cron_schedules', 'WC_YottaPay_Filters::add_schedule_interval');

        // - Gateway
        add_filter('woocommerce_payment_gateways', 'WC_YottaPay_Filters::add_gateway');

        // - UI modification
        add_filter('plugin_row_meta', 'WC_YottaPay_Filters::add_links_to_plugin_row_meta', 10, 2);
        add_filter('plugin_action_links_' . YOTTAPAY_PLUGIN_BASENAME, 'WC_YottaPay_Filters::add_links_to_plugin_action');
        add_filter('woocommerce_gateway_title', 'WC_YottaPay_Filters::set_extended_title', 10, 2);
        add_filter('woocommerce_gateway_icon', 'WC_YottaPay_Filters::set_icon', 10, 2 );
        add_filter('woocommerce_order_details_before_order_table','WC_YottaPay_Filters::modify_woocommerce_order_details_before_order_table', 10, 1);
        add_filter('woocommerce_order_details_after_order_table', 'WC_YottaPay_Filters::modify_woocommerce_order_details_after_order_table', 10, 1);
        add_filter('woocommerce_my_account_my_orders_actions', 'WC_YottaPay_Filters::modify_woocommerce_my_account_my_orders_actions', 10, 2);
        if (version_compare(WC_VERSION, '5.8', '>='))
        {
            add_filter('woocommerce_order_actions', 'WC_YottaPay_Filters::modify_woocommerce_order_actions', 10, 2);
        }
    }
    
    //---------------------------------------------------
    //  CALLBACK
    //---------------------------------------------------

    public static function add_schedule_interval($schedules)
    {
        $extended_schedules = WC_YottaPay_Scheduler::add_schedule_interval(
            $schedules,
            'yottapay_five_min_schedule_interval',
            60 * 5,
            'Yotta Pay (5 min interval)'
        );
    
        return $extended_schedules;
    }

    public static function add_gateway($gateways)
    {
        $gateways[] = 'WC_Gateway_YottaPay';
        return $gateways;
    }

    public static function add_links_to_plugin_action($actions)
    {
        return WC_YottaPay_Frontend_Common::add_action_links($actions);
    }

    public static function set_extended_title($title, $payment_id)
    {
        return WC_YottaPay_Frontend_Common::get_payment_method_extended_title($title, $payment_id);
    }

    public static function set_icon($icon, $gateway_id)
    {   
        return WC_YottaPay_Frontend_Common::get_payment_method_icon_html($icon, $gateway_id);
    }

    public static function modify_woocommerce_order_details_before_order_table($order)
    {
        return WC_YottaPay_Frontend_Loyalty::add_data_to_order($order);
    }

    public static function modify_woocommerce_order_details_after_order_table($order)
    {
        return WC_YottaPay_Frontend_Refund::add_refund_btn_to_order($order);
    }

    public static function modify_woocommerce_my_account_my_orders_actions($actions, $order)
    {
        return WC_YottaPay_Frontend_Refund::add_refund_btn_to_actions($actions, $order);
    }

    public static function modify_woocommerce_order_actions($actions, $order)
    {
        return WC_YottaPay_Frontend_Refund::disable_admin_refund_button($actions, $order);
    }

    public static function add_links_to_plugin_row_meta($links, $file)
    {
        return WC_YottaPay_Frontend_Common::custom_plugin_row_meta($links, $file);
    }
}
