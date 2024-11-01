<?php
/**
 * Plugin Name: Yotta Pay Payment Gateway
 * Requires Plugins: woocommerce
 * Plugin URI: https://yottapay.co.uk/online-checkout
 * Description: Yotta Pay is the fastest and most secure way to accept payments via UK bank account.
 * Version: 3.0.2
 * Author: Yotta Pay
 * Author URI: https://yottapay.co.uk/
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Requires PHP: 7.0
 * Requires at least: 5.3
 * Tested up to: 6.6
 * WC requires at least: 3.9
 * WC tested up to: 9.3
 * Text Domain: yottapay-payment-gateway
 */

if (!defined('ABSPATH'))
{
    exit;
}

//Constants
define('YOTTAPAY_PLUGIN_VERSION', '3.0.2');
define('YOTTAPAY_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('YOTTAPAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('YOTTAPAY_PLUGIN_BASENAME', plugin_basename(__FILE__));

//Base hooks
add_action('before_woocommerce_init', 'yottapay_payment_gateway_declare_hpos_compatibility');
add_action('plugins_loaded', 'yottapay_payment_gateway_plugin_loaded');

//---------------------------------------------------
//  CALLBACK
//---------------------------------------------------

function yottapay_payment_gateway_plugin_loaded()
{   
    require_once YOTTAPAY_PLUGIN_PATH . 'includes/common/workers/class-wc-yottapay-load-worker.php';
    
    if (WC_YottaPay_Load_Worker::verify_woocommerce_compatibility() == false)
    {
        return;
    }

    WC_YottaPay_Load_Worker::require_files();

    WC_YottaPay_Actions::add_actions();

    WC_YottaPay_Filters::add_filters();

    WC_YottaPay_Actions::add_api_hooks();
}

function yottapay_payment_gateway_declare_hpos_compatibility()
{
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class))
    {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__,
            true
        );
	}
}
