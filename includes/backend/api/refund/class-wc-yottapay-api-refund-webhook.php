<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay API Refund Webhook class
 */
class WC_YottaPay_API_Refund_Webhook
{
    /**
     * Process request
     */
    public static function execute()
    {
        try
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Refund_Webhook', 'execute', 'Start');

            // - Get data from request
            $request_data = WC_YottaPay_API_Worker::get_request_data();
            
            // - Check required fields and get order
            $order = self::check_request_data($request_data);            

            // - Set order status
            $order->update_status('refunded');

            // - Return success
            $result_data = 'OK';
            $result_http_status_code = 200;
        }
        catch (Exception $e)
        {
            $exception_message_template = (
                'Error WC_YottaPay_API_Refund_Webhook [v. %3$s]: %4$s'
                . '%1$srequest_data: %2$s'
            );

            $exception_message = sprintf(
                $exception_message_template,
                PHP_EOL,
                isset($request_data) ? print_r($request_data, TRUE) : '',
                YOTTAPAY_PLUGIN_VERSION,
                $e->getMessage()
            );

            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Refund_Webhook', 'execute', $exception_message);

            // - Return exception
            $result_data = [
                'message' => $exception_message,
                'trace' => null
            ];
            $result_http_status_code = 500;
        }
        finally
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Refund_Webhook', 'execute', 'End');
            
            $result = [
                'data' => $result_data,
                'http_status_code' => $result_http_status_code,
            ];

            return $result;
        }
    }
    
    //---------------------------------------------------
    //  PRIVATE
    //---------------------------------------------------

    /**
     * Check required fields, then return order
     */
    private static function check_request_data($request_data)
    {
        // - Check required keys
        if (!array_key_exists('userId', $request_data) ||
            !array_key_exists('orderId', $request_data))
        {
            throw new Exception('Missing required parameters in request.');
        }

        // - Get order
        $splited_shop_transaction_identifier = explode('-', $request_data['orderId']);
        $order_id = end($splited_shop_transaction_identifier);
        $order = wc_get_order($order_id);

        // - Check order
        if (!$order)
        {
            throw new Exception('No order.');
        }

        // - Get user_id option
        $options = WC_YottaPay_Options::get_gateway_options();
        $source_user_id = $options['user_id'];

        // - Verify source userId
        if ($source_user_id == '')
        {
            throw new Exception('Empty user_id');
        }

        // - Verify requested userId
        if (sanitize_text_field($request_data['userId']) != $source_user_id)
        {
            throw new Exception('Unknown userId');
        }

        // - Verify order status
        $order_status = $order->get_status();
        if ($order_status != 'processing' && $order_status != 'completed')
        {
            throw new Exception('Invalid order status for refund: ' . $order_status);
        }

        // - Verify Yotta Pay transaction id in order
        $order_transaction_id = $order->get_transaction_id();
        if ($order_transaction_id == '')
        {
            throw new Exception('No transaction id in order');
        }

        // - Return order
        return $order;
    }
}
