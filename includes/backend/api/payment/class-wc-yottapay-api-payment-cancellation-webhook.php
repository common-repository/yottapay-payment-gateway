<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay API Payment Cancellation Webhook class
 */
class WC_YottaPay_API_Payment_Cancellation_Webhook
{
    /**
     * Verify order and return cancellation status
     */
    public static function execute()
    {
        try 
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Payment_Cancellation_Webhook', 'execute', 'Start');
            
            // - Return 'true' to interrupt the payment as default result (200 status code)
            $result_data = [
                'result' => true,
            ];
            $result_http_status_code = 200;

            // - Get parameter from request
            $order_id = isset($_GET['order_id']) ? wc_clean(wp_unslash($_GET['order_id'])) : '';

            // - Check parameter is not empty
            if ($order_id == '')
            {
                throw new Exception('No order_id parameter');
            }

            // - Log
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Payment_Cancellation_Webhook', 'execute', 'order_id', '#' . $order_id);

            // - Load order
            $order = wc_get_order($order_id);
            if (!$order)
            {
                throw new Exception('No order');
            }

            // - Verify order status
            $order_status = $order->get_status();
            if ($order_status != 'pending')
            {
                $message_template = (
                    'Invalid order status to payment intent.'
                    . '%1$sorder_id: %2$s'
                    . '%1$sorder_status: %3$s'
                );

                $message = sprintf(
                    $message_template,
                    PHP_EOL,
                    $order_id,
                    $order_status
                );

                throw new Exception($message);
            }

            // - Return 'false' so as not to interrupt the payment
            $result_data['result'] = false;
        }
        catch (Exception $e)
        {
            // - Log
            $exception_message = ('Error WC_YottaPay_API_Payment_Cancellation_Webhook [v. '. YOTTAPAY_PLUGIN_VERSION . ']: ' . $e->getMessage());

            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Payment_Cancellation_Webhook', 'execute', $exception_message);

            // - Set message and status code
            $result_data = [
                'result' => true,
                'message' => $exception_message,
                'trace' => null
            ];
            $result_http_status_code = 500;
        }
        finally
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Payment_Cancellation_Webhook', 'execute', 'interrupt the payment', ($result_data['result'] ? 'true' : 'false'));
            
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Payment_Cancellation_Webhook', 'execute', 'End');

            $result = [
                'data' => $result_data,
                'http_status_code' => $result_http_status_code,
            ];

            return $result;
        }
    }
}
