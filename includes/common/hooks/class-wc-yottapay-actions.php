<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay Action Manager class
 */
class WC_YottaPay_Actions
{
    /**
     * Add actions
     */
    public static function add_actions()
    {
        // - Endpoints and templates
        add_action('init', 'WC_YottaPay_Actions::add_rewrite_endpoints');
        add_action('woocommerce_account_yottapay-refund-request-form_endpoint', 'WC_YottaPay_Actions::include_template_refund_request');

        // - Scripts
        add_action('admin_head', 'WC_YottaPay_Actions::admin_print_scripts');
        add_action('wp_head', 'WC_YottaPay_Actions::print_scripts');
        add_action('woocommerce_review_order_before_payment', 'WC_YottaPay_Actions::print_scripts_to_checkout');

        // - Scheduler
        add_action('wp', 'WC_YottaPay_Actions::schedule_events');
        add_action('yottapay_scheduled_action_reload_client_user_data', 'WC_YottaPay_Actions::execute_scheduled_action_reload_client_user_data');

        // - Options
        add_action('woocommerce_update_options_payment_gateways_' . WC_YottaPay_Common::GATEWAY_ID, 'WC_YottaPay_Actions::process_admin_options');
        add_action('updated_option', 'WC_YottaPay_Actions::verify_gateway_option_values', 10, 3);
        
        // - UI modification
        add_action('woocommerce_settings_checkout', 'WC_YottaPay_Actions::modify_woocommerce_settings_checkout');

        // - Email
        add_action('woocommerce_email_order_meta', 'WC_YottaPay_Actions::add_data_to_email', 10, 4);
    }

    /**
     * Add gateway API hooks
     */
    public static function add_api_hooks()
    {
        // - Authorize
        add_action('woocommerce_api_yottapay_authorize', 'WC_YottaPay_Actions::api_authorize');
        add_action('woocommerce_api_yottapay_authorize_webhook', 'WC_YottaPay_Actions::api_authorize_webhook');

        // - Payment
        add_action('woocommerce_api_yottapay_payment_cancellation_webhook', 'WC_YottaPay_Actions::api_payment_cancellation_webhook');
        add_action('woocommerce_api_yottapay_payment_webhook', 'WC_YottaPay_Actions::api_payment_webhook');

        // - Refund
        add_action('woocommerce_api_yottapay_refund', 'WC_YottaPay_Actions::api_refund');
        add_action('woocommerce_api_yottapay_refund_webhook', 'WC_YottaPay_Actions::api_refund_webhook');

        // - Loyalty
        add_action('woocommerce_api_yottapay_loyalty', 'WC_YottaPay_Actions::api_loyalty');
    }
    
    //---------------------------------------------------
    //  CALLBACK
    //---------------------------------------------------

    public static function add_rewrite_endpoints()
    {
        WC_YottaPay_Refund_Worker::add_myaccount_refund_endpoint();
    }

    public static function include_template_refund_request()
    {
        require_once YOTTAPAY_PLUGIN_PATH . 'templates/refund-request.php';
    }

    public static function admin_print_scripts()
    {
        WC_YottaPay_Frontend_Common::admin_print_scripts();
    }

    public static function print_scripts()
    {
        WC_YottaPay_Frontend_Common::print_scripts();
    }

    public static function print_scripts_to_checkout()
    {
        WC_YottaPay_Frontend_Payment::print_scripts();
    }

    public static function schedule_events()
    {
        WC_YottaPay_Scheduler::schedule_event(
            'yottapay_five_min_schedule_interval',
            'yottapay_scheduled_action_reload_client_user_data'
        );
    }

    public static function execute_scheduled_action_reload_client_user_data()
    {
        WC_YottaPay_Userdata_Worker::reload_client_userdata();
    }

    public static function process_admin_options()
    {
        $gateway = WC_Gateway_YottaPay::get_instance();
        $gateway->process_admin_options();  
    }

    public static function verify_gateway_option_values($option_name, $old_value, $value)
    {   
        WC_YottaPay_Options::verify_gateway_option_values($option_name, $old_value, $value);
    }

    public static function modify_woocommerce_settings_checkout()
    {
        WC_YottaPay_Frontend_Authorize::modify_settings_checkout();
    }

    public static function add_data_to_email($order, $sent_to_admin, $plain_text, $email)
    {
        WC_YottaPay_Frontend_Loyalty::add_data_to_email($order, $email);
    }

    public static function api_authorize()
    {
        $result = WC_YottaPay_API_Authorize::execute();

        wp_send_json($result['data'], $result['http_status_code']);
    }

    public static function api_authorize_webhook()
    {        
        $result = WC_YottaPay_API_Authorize_Webhook::execute();

        wp_send_json($result['data'], $result['http_status_code']);
    }

    public static function api_payment_cancellation_webhook()
    {
        $result = WC_YottaPay_API_Payment_Cancellation_Webhook::execute();

        wp_send_json($result['data'], $result['http_status_code']);
    }

    public static function api_payment_webhook()
    {        
        $result = WC_YottaPay_API_Payment_Webhook::execute();

        wp_send_json($result['data'], $result['http_status_code']);
    }

    public static function api_refund()
    {
        $result = WC_YottaPay_API_Refund::execute();

        wp_send_json($result['data'], $result['http_status_code']);
    }

    public static function api_refund_webhook()
    {        
        $result = WC_YottaPay_API_Refund_Webhook::execute();

        wp_send_json($result['data'], $result['http_status_code']);
    }

    public static function api_loyalty()
    {        
        $result = WC_YottaPay_API_Loyalty::execute();

        wp_send_json($result['data'], $result['http_status_code']);
    }
}
