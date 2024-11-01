<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay Load Worker class
 */
class WC_YottaPay_Load_Worker
{
    /**
     * Require plugin files
     */
    public static function require_files()
    {
        // - Gateway
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/class-wc-gateway-yottapay.php';

        // - Commons
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/common/class-wc-yottapay-common.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/common/class-wc-yottapay-exception.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/common/class-wc-yottapay-logger.php';

        // - Options
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/common/options/class-wc-yottapay-options.php';

        // - Scheduler
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/common/scheduler/class-wc-yottapay-scheduler.php';

        // - Workers
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/common/workers/class-wc-yottapay-load-worker.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/common/workers/class-wc-yottapay-options-worker.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/common/workers/class-wc-yottapay-api-worker.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/common/workers/class-wc-yottapay-userdata-worker.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/common/workers/class-wc-yottapay-payment-worker.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/common/workers/class-wc-yottapay-refund-worker.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/common/workers/class-wc-yottapay-loyalty-worker.php';

        // - Frontend
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/frontend/class-wc-yottapay-frontend-common.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/frontend/class-wc-yottapay-frontend-authorize.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/frontend/class-wc-yottapay-frontend-payment.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/frontend/class-wc-yottapay-frontend-loyalty.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/frontend/class-wc-yottapay-frontend-refund.php';

        // - API
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/backend/api/class-wc-yottapay-api-endpoints.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/backend/api/class-wc-yottapay-api-webhooks.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/backend/api/authorize/class-wc-yottapay-api-authorize.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/backend/api/authorize/class-wc-yottapay-api-authorize-webhook.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/backend/api/userdata/class-wc-yottapay-api-userdata.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/backend/api/payment/class-wc-yottapay-api-payment.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/backend/api/payment/class-wc-yottapay-api-payment-cancellation-webhook.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/backend/api/payment/class-wc-yottapay-api-payment-webhook.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/backend/api/refund/class-wc-yottapay-api-refund.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/backend/api/refund/class-wc-yottapay-api-refund-webhook.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/backend/api/loyalty/class-wc-yottapay-api-loyalty.php';

        // - Hooks
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/common/hooks/class-wc-yottapay-actions.php';
        require_once YOTTAPAY_PLUGIN_PATH . 'includes/common/hooks/class-wc-yottapay-filters.php';
    }

    /**
     * Verify WooCommerce exists and actual
     */
    public static function verify_woocommerce_compatibility()
    {     
        if (!class_exists('WooCommerce'))
        {
            add_action('admin_notices', 'WC_YottaPay_Load_Worker::admin_notices_woocommerce_missing');             
            return false;
        }

        if (version_compare(WC_VERSION, '3.9', '<'))
        {
            add_action('admin_notices', 'WC_YottaPay_Load_Worker::admin_notices_woocommerce_not_supported');        
            return false;
        }

        return true;
    }

    /**
     * WooCommerce fallback notice
     */
    public static function admin_notices_woocommerce_missing()
    {
        $escaped_html = esc_html__(
            'Yotta Pay requires WooCommerce to be installed and active. You can download %s here.',
            'yottapay-payment-gateway'
        );

        echo (
            '<div class="error"><p><strong>'
            . sprintf($escaped_html, '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>')
            . '</strong></p></div>'
        );
    }

    /**
     * WooCommerce not supported fallback notice
     */
    public static function admin_notices_woocommerce_not_supported()
    {
        $escaped_html = esc_html__(
            'Yotta Pay requires WooCommerce %1$s or greater to be installed and active. WooCommerce %2$s is no longer supported.',
            'yottapay-payment-gateway'
        );

        echo (
            '<div class="error"><p><strong>'
            . sprintf($escaped_html, '3.9', WC_VERSION)
            . '</strong></p></div>'
        );
    }
}
