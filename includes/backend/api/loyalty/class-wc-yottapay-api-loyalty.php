<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay API Loyalty class
 */
class WC_YottaPay_API_Loyalty
{
    /**
     * Execute create deferred points request to Yotta Pay API
     * Return result containing URL to redirect to Yotta Pay loyalty page
     */
    public static function execute($email_order = null)
    {
        try
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Loyalty', 'execute', 'Start');

            // - Init default result and http status code
            $result_data = [
                'status' => '0',
                'link' => '',
                'error' => 'Request failed. Please contact the store owner to solve the problem.'
            ];
            $result_http_status_code = 200;

            // - Verify request to email
            if ($email_order != null)
            {
                // -- Order from hook
                $order = $email_order;
            }
            else
            {
                // -- Order from request
                $order = self::validate_request_data();
            }
            
            // - Check request available to execute
            WC_YottaPay_Loyalty_Worker::check_create_deferred_available($order);

            // - Get request body 
            $request_body = self::get_request_body($order);

            // - Make request
            $response_data = WC_YottaPay_API_Worker::make_post_request(WC_YottaPay_API_Endpoints::LOYALTY_CREATE_DEFERRED, $request_body);

            // - Process response
            $result_data = self::process_response($response_data);
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Loyalty', 'execute', $e->getMessage());
        }
        finally
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Loyalty', 'execute', 'End');
            
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
        $post_data = WC_YottaPay_API_Worker::get_request_data();

        // - Verify required parameters
        if (!isset($post_data['order_id']))
        {
            throw new Exception('Missing required parameter order_id');
        }

        // - Check order exists
        $order_id = sanitize_text_field($post_data['order_id']);
        $order = wc_get_order($order_id);
        if (!$order)
        {
            throw new Exception('No order.');
        }

        // - Return order
        return $order;
    }

    /**
     * Return request body
     */
    private static function get_request_body($order)
    {   
        // - Get plugin options
        $options = WC_YottaPay_Options::get_gateway_options();

        // - Check customer
        if ($order->get_customer_id() != '0')
        {
            // -- For account
            $clientUserId = '1_' . $order->get_customer_id();
        }
        else
        {
            // -- For guest
            $clientUserId = '0_' . $order->get_id();
        }

        // - Check bonus and set loyalty amount
        $bonus = (($options['loyalty_bonus'] * $order->get_total()) / 100);
        $roundedBonus = intval(number_format(round($bonus, 2, PHP_ROUND_HALF_UP), 2, '', '')); //without point

        if ($roundedBonus < 1)
        {
            $roundedBonus = 1;
        }

        $roundedMaxBonus = intval(number_format(round($options['loyalty_max_bonus'], 2, PHP_ROUND_HALF_UP), 2, '', '')); //without point

        if (($roundedMaxBonus > 0) && ($roundedBonus > $roundedMaxBonus))
        {
            $loyaltyAmount = $roundedMaxBonus;
        }
        else
        {
            $loyaltyAmount = $roundedBonus;
        }

        // - Build request body
        $body = [
            'userAccessToken' => $options['user_token'],
            'customerId' => $clientUserId,
            'amount' => $loyaltyAmount,
            'comment' => $order->get_id(),
        ];

        // - Return request body
        return $body;
    }

    /**
     * Return result of response processing
     */
    private static function process_response($response_data)
    {
        // - Verify required parameters
        if (!array_key_exists('result', $response_data))
        {
            throw new Exception('Missing required parameters in Yotta Pay API response.');
        }

        // - Set result
        $result = array(
            'status' => '1',
            'link' => sanitize_text_field($response_data['result']),
            'error' => ''
        );

        // - Return result
        return $result;
    }    
}
