<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay API Payment class
 */
class WC_YottaPay_API_Payment
{
    /**
     * Execute request with placed order data to Yotta Pay API
     * Return result containing URL to redirect to Yotta Pay payment page
     */
    public static function execute($order_id, $is_sc = false)
    {
        try
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Payment', 'execute', 'Start');
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Payment', 'execute', 'order_id', '#' . $order_id);

            // - Init default result and http status code
            $result_data = [
                'result' => 'fail',
                'redirect' => '',
                'response_data' => null //to supercheckout only
            ];
            $result_http_status_code = 200;

            // - Get order by id
            $order = wc_get_order($order_id);
            if (!$order)
            {
                throw new Exception('No order with the order_id');
            }

            // - If not valid throw WC_YottaPay_Exception to notify user
            WC_YottaPay_Payment_Worker::validate_billing_country($order, $is_sc);
            WC_YottaPay_Payment_Worker::validate_order_currency($order, $is_sc);
            WC_YottaPay_Payment_Worker::validate_minimum_order_amount($order, $is_sc);
            
            // - Get request body 
            $request_body = self::get_request_body($order, $is_sc);

            // - Make request
            $response_data = WC_YottaPay_API_Worker::make_post_request(WC_YottaPay_API_Endpoints::ORDER_NEW, $request_body);

            // - Process response
            $result_data = self::process_response($response_data);
        }
        catch (WC_YottaPay_Exception $e)
        {
            // - Show notice for customer
            wc_add_notice($e->getMessage(), 'error');

            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Payment', 'execute', $e->getMessage());
        }
        catch (Exception $e)
        {
            // - Show notice for customer
            wc_add_notice('Payment was failed. Please contact the store owner to solve the problem.', 'error');

            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Payment', 'execute', $e->getMessage());
        }
        finally
        {

            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Payment', 'execute', 'End');

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
     * Return request body
     */
    private static function get_request_body($order, $is_sc)
    {
        // - Check for payment initialization source (supercheckout or order pay or checkout)
        if ($is_sc) //From supercheckout
        {
		    $order_total = $order->get_total();
        }
        elseif (isset($_GET['pay_for_order'])) //From order pay page
        {
            $query_parameter_key = isset($_GET['key']) ? wc_clean(wp_unslash($_GET['key'])) : '';
            
            if ($query_parameter_key == '')
            {
                throw new Exception('Invalid order payment page');
            }

            $order_total = $order->get_total();
        }
        else //From checkout page
        {
		    $order_total = WC()->cart->get_total(false);
        }

        // - Add gateway discount to order and get discounted total
        $order_total = WC_YottaPay_Payment_Worker::set_gateway_discount_to_order($order, $order_total);

        // - Get plugin options
        $options = WC_YottaPay_Options::get_gateway_options();

        // - Set request body fields

        $user_access_token = $options['user_token'];

        $transaction_id = (
            WC_YottaPay_Common::PLATFORM_PREFIX
            . '-'
            . $order->get_customer_id()
            . '-'
            . $order->get_id()
        );

        $reference = $transaction_id;

        $amount = number_format(round($order_total, 2, PHP_ROUND_HALF_UP), 2, '.', '');

        $customer_id = $order->get_customer_id();

        $redirect_url_success = (
            get_site_url()
            . '/checkout/order-received/'
            . $order->get_id()
            . '?key='
            . $order->get_order_key()
        );

        $redirect_url_fail = (
            get_site_url()
            . '/checkout/order-received/'
            . $order->get_id()
            . '?key='
            . $order->get_order_key()
            . '&failed=1'
        );

        $cancellation_url = (
            get_site_url()
            . WC_YottaPay_API_Webhooks::ORDER_CHECK_TO_CANCELLATION
            . '?order_id='
            . $order->get_id()
        );

        $webhook_url = get_site_url() . WC_YottaPay_API_Webhooks::ORDER_NEW;

        // - Build request body
        $body = [
            'userAccessToken' => $user_access_token,
            'transactionId' => $transaction_id,
            'reference' => $reference,
            'amount' => $amount,
            'customerId' => $customer_id,
            'redirectUrlSuccess' => $redirect_url_success,
            'redirectUrlFail' => $redirect_url_fail,
            'cancellationUrl' => $cancellation_url,
            'webhookUrl' => $webhook_url,
            'tagmanagerId' => null,
            'otherRequestParams' => null,
        ];

        // - Set GTM fields if option contains value
        if ($options['gtm_id'] != '')
        {
            $body['tagmanagerId'] = $options['gtm_id'];
            $body['otherRequestParams'] = WC_YottaPay_Payment_Worker::get_other_request_params();
        }

        // - Return request body
        return $body;
    }

    /**
     * Return result of response processing
     */
    private static function process_response($response_data)
    {
        // - Check for required keys
        if (!array_key_exists('url', $response_data))
        {
            throw new Exception('Missing required key');
        }

        // - Set result fields
        $result =  [
            'result' => 'success',
            'redirect' => sanitize_text_field($response_data['url']),
            'response_data' => $response_data, //to supercheckout only
        ];

        // - Return result
        return $result;
    }
}
