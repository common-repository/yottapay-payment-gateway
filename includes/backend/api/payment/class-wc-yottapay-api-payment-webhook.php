<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay API Payment Webhook class
 */
class WC_YottaPay_API_Payment_Webhook
{
    /**
     * Process request
     */
    public static function execute()
    {
        try
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Payment_Webhook', 'execute', 'Start');

            // - Get data from request
            $request_data = WC_YottaPay_API_Worker::get_request_data();

            // - Check required fields and get order
            $order = self::check_request_data($request_data);

            // - Check result code from request and update order status
            self::process_order($request_data, $order);
            
            // - Return success
            $result_data = 'OK';
            $result_http_status_code = 200;
        }
        catch (Exception $e)
        {
            $exception_message_template = (
                'Error WC_YottaPay_API_Payment_Webhook [v. %3$s]: %4$s'
                . '%1$srequest_data: %2$s'
            );

            $exception_message = sprintf(
                $exception_message_template,
                PHP_EOL,
                isset($request_data) ? print_r($request_data, TRUE) : '',
                YOTTAPAY_PLUGIN_VERSION,
                $e->getMessage()
            );

            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Payment_Webhook', 'execute', $exception_message);

            // - Return exception
            $result_data = [
                'message' => $exception_message,
                'trace' => null
            ];
            $result_http_status_code = 500;
        }
        finally
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Payment_Webhook', 'execute', 'End');
            
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
        // - Verify required keys
        if (!array_key_exists('orderId', $request_data) ||
            !array_key_exists('yottaTransactionId', $request_data) ||
            !array_key_exists('userId', $request_data) ||
            !array_key_exists('success', $request_data))
        {
            throw new Exception('Missing required keys');
        }

        // - Parse requested orderId and get order
        $splited_shop_transaction_identifier = explode('-', sanitize_text_field($request_data['orderId']));
        $order_id = end($splited_shop_transaction_identifier);
        $order = wc_get_order($order_id);

        // - Check order
        if (!$order)
        {
            throw new Exception('No order');
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

        // - Return order
        return $order;
    }

    /**
     * Check operation result code from request and update order status
     */
    private static function process_order($request_data, $order)
    {
        // - Get order status
        $order_status = $order->get_status();

        // - Verify response status
        $response_status = sanitize_text_field($request_data['success']);
        
        if (boolval($response_status) && wc_strtolower($response_status) != 'false')
        {
            // -- Verify order status
            if ($order_status != 'pending'
                && $order_status != 'failed'
                && $order_status != 'cancelled')
            {
                $exception_message_template = ('Invalid order status to processing: %1$.');

                $exception_message = sprintf(
                    $exception_message_template,
                    $order_status
                );

                throw new Exception($exception_message);
            }

            // -- Set transaction id
            $order->set_transaction_id(sanitize_text_field($request_data['yottaTransactionId']));
            
            // -- Update order status
            $order->update_status('processing');
        }
        else
        {
            // -- Verify order status
            if ($order_status == 'pending')
            {
                $order->update_status('failed');
            }
        }
    }
}
