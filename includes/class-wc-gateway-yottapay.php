<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC_Gateway_YottaPay class
 *
 * @extends WC_Payment_Gateway
 */
class WC_Gateway_YottaPay extends WC_Payment_Gateway
{
    /**
     * Plugin instance
     */
    protected static $instance = null;

    /**
     * Access plugin instance
     */
    public static function get_instance()
    {
        if (NULL === self::$instance)
        {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Gateway constructor
     */
    public function __construct()
    {
        $this->form_fields = WC_YottaPay_Options::get_form_fields();

        $this->id = WC_YottaPay_Common::GATEWAY_ID;
        $this->method_title = WC_YottaPay_Frontend_Common::get_payment_method_title();
        $this->method_description = WC_YottaPay_Frontend_Common::get_payment_method_description();
        $this->title = WC_YottaPay_Frontend_Common::get_payment_method_frontend_title();
        $this->description = WC_YottaPay_Frontend_Common::get_payment_method_frontend_description($this->get_option('checkout_description_type'));        
        $this->has_fields = false;        

        $this->init_settings();
    }

    /**
     * Process payment
     */
    public function process_payment($order_id)
    {
        $result = WC_YottaPay_API_Payment::execute($order_id);

        return $result['data'];
    }
}
