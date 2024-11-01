<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay API Refund class
 */
class WC_YottaPay_API_Refund
{
    /**
     * Execute request to Yotta Pay API
     * Return result status
     */
    public static function execute()
    {
        try
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Refund', 'execute', 'Start');

            // - Init default result and http status code
            $result_data = [
                'status' => '0',
                'error' => 'Request failed. Please contact the store owner to solve the problem.'
            ];
            $result_http_status_code = 200;

            //Validate request data
            $validated_data = self::validate_request_data();

            // - Get request body 
            $request_body = self::get_request_body($validated_data);

            // - Make request
            $response_data = WC_YottaPay_API_Worker::make_post_request(WC_YottaPay_API_Endpoints::REFUND_NEW, $request_body);
                
            // - Process response
            $result_data = self::process_response($response_data);
        }
        catch (WC_YottaPay_Exception $e)
        {
            // - Customer notice
            $result_data['error'] = $e->getMessage();

            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Refund', 'execute', $e->getMessage());
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Refund', 'execute', $e->getMessage());
        }
        finally
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Refund', 'execute', 'End');

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
     * Validate request data
     */
    private static function validate_request_data()
    {
        // - Get request data
        $post_data = wp_unslash($_POST);

        WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Refund', 'execute', 'postdata', print_r($post_data, TRUE));

        // - Verify nonce
        if (!isset($post_data['yottapay_refund_request_nonce']))
        {
            throw new Exception('Missing required parameters.');
        }
        if (!wp_verify_nonce($post_data['yottapay_refund_request_nonce'], 'yottapay_refund_request_form_nonce_action'))
        {
            throw new Exception('Verify nonce exception');
        }

        // - Verify required parameters
        if (!isset($post_data['order_id']) ||
            !isset($post_data['refund_receiver_full_name']) ||
            !isset($post_data['refund_receiver_account_number']) ||
            !isset($post_data['refund_receiver_sort_code']) ||
            !isset($post_data['refund_receiver_comment']))
        {
            throw new Exception('Missing required parameters');
        }

        // - Check order
        $order_id = sanitize_text_field($post_data['order_id']);
        $order = wc_get_order($order_id);
        if (!$order)
        {
            throw new Exception('No order');
        }

        // - Check order status
        $order_status = $order->get_status();
        if ($order_status != 'processing' && $order_status != 'completed')
        {
            throw new Exception('Invalid order status for refund: ' . $order_status);
        }

        // - Check for Yotta Pay transaction id in order
        $order_transaction_id = $order->get_transaction_id();
        if ($order_transaction_id == '')
        {
            throw new Exception('No transaction id in order');
        }

        // - Check customer
        if ($order->get_customer_id() != get_current_user_id()  ||
            get_current_user_id() == '0' ||
            !is_user_logged_in())
        {
            throw new Exception('Another customer');
        }

        // - Set validated data
        $validated_data = [
            'order' => $order,
            'refund_receiver_full_name' => trim(sanitize_text_field($post_data['refund_receiver_full_name'])),
            'refund_receiver_account_number' => trim(sanitize_text_field($post_data['refund_receiver_account_number'])),
            'refund_receiver_sort_code' => trim(sanitize_text_field($post_data['refund_receiver_sort_code'])),
            'refund_receiver_comment' => trim(sanitize_text_field($post_data['refund_receiver_comment']))
        ];

        // - Check fields lengts
        if (strlen($validated_data['refund_receiver_full_name']) == 0 ||
            strlen($validated_data['refund_receiver_full_name']) > 64)
        {
            throw new Wc_YottaPay_Exception('Account holder name is required field (length up to 64 characters)');
        }
        if (strlen($validated_data['refund_receiver_account_number']) != 8)
        {
            throw new Wc_YottaPay_Exception('Account number is required field (length 8 characters)');
        }
        if (strlen($validated_data['refund_receiver_sort_code']) != 6)
        {
            throw new Wc_YottaPay_Exception('Sort code is required field (length 6 characters)');
        }
        if (strlen($validated_data['refund_receiver_comment']) == 0 ||
            strlen($validated_data['refund_receiver_comment']) > 1000)
        {
            throw new Wc_YottaPay_Exception('Comment is required field (length up to 1000 characters)');
        }

        // - Check for numeric
        $accountNumberCleared = preg_replace('/[^0-9]/', '', $validated_data['refund_receiver_account_number']);
        $sortCodeCleared = preg_replace('/[^0-9]/', '', $validated_data['refund_receiver_sort_code']);
        if ((strlen($accountNumberCleared) != 8) ||
            (strlen($sortCodeCleared) != 6))
        {
            throw new Wc_YottaPay_Exception('Incorrect format of Account number or Sort code');
        }

        //Return validated data
        return $validated_data;
    }

    /**
     * Return request body
     */
    private static function get_request_body($validated_data)
    {
        // - Get plugin options
        $options = WC_YottaPay_Options::get_gateway_options();

        // - Get order
        $order = $validated_data['order'];

        // - Set request body fields
        $user_access_token = $options['user_token'];
        $yotta_transaction_id = $order->get_transaction_id();
        $external_order_id = WC_YottaPay_Common::PLATFORM_PREFIX . '-' . $order->get_customer_id() . '-' . $order->get_id();
        $webhook_url = get_site_url() . WC_YottaPay_API_Webhooks::REFUND_NEW;
        $receiver_full_name = $validated_data['refund_receiver_full_name'];
        $receiver_account_number = $validated_data['refund_receiver_account_number'];
        $receiver_sort_code = $validated_data['refund_receiver_sort_code'];
        $reason = $validated_data['refund_receiver_comment'];

        // - Build request body
        $body = [
            'userAccessToken' => $user_access_token,
            'yottaTransactionId' => $yotta_transaction_id,
            'externalOrderId' => $external_order_id,
            'webhookUrl' => $webhook_url,
            'receiverFullName' => $receiver_full_name,
            'receiverAccountNumber' => $receiver_account_number,
            'receiverSortCode' => $receiver_sort_code,
            'reason' => $reason,
        ];

        // - Return request body
        return $body;
    }

    /**
     * Return result of response processing
     */
    private static function process_response($response_data)
    {
        // - Check required fields
        if (!array_key_exists('success', $response_data))
        {
            throw new Exception('Missing required parameters in Yotta Pay API response');
        }

        // - Check ok status
        $response_status = sanitize_text_field($response_data['success']);
        if (!(boolval($response_status) && wc_strtolower($response_status) != 'false'))
        {
            throw new Exception('Request has been failed');
        }

        // - Show notice for customer
        wc_add_notice('Request has been submitted successfully', 'success');

        // - Set result
        $result = [
            'status' => '1',
            'error' => '',
        ];

        // - Return result
        return $result;
    }
}
