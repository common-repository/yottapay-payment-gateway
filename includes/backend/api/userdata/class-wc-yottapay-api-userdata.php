<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay API Userdata class
 */
class WC_YottaPay_API_Userdata
{
    /**
     * Execute reload client userdata request to Yotta Pay API
     * Return result containing URL to redirect to Yotta Pay payment page
     */
    public static function execute()
    {
        try
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Userdata', 'execute', 'Start');

            // - Init default result and http status code
            $result_data = [
                'status' => '0',
                'userdata' => [],
                'error' => 'Request failed.'
            ];
            $result_http_status_code = 200;

            // - Get plugin options
            $options = WC_YottaPay_Options::get_gateway_options();

            // - Make sure user_id exists
            if ($options['user_id'] == '')
            {
                WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Userdata', 'execute', 'empty user_id');

                return;
            }

            // - Build request url
            $request_url = WC_YottaPay_API_Endpoints::USERDATA_UPDATE_AND_GET . '/' . $options['user_id'];

            // - Perform request without logging
            $response_data = WC_YottaPay_API_Worker::make_get_request($request_url, 20, false);

            // - Process response data
            $result_data = self::process_response($response_data);
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Userdata', 'execute', $e->getMessage());
        }
        finally
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Userdata', 'execute', 'End');

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
     * Verify response data and update yottapay_meta_data option
     */
    private static function process_response($response_data)
    {
        try
        {
            // - Check required keys
            if (!array_key_exists('tradingName', $response_data))
            {
                throw new Exception('Missing required parameter tradingName in Yotta Pay API response.');
            }
            if (!array_key_exists('stripeDiscount', $response_data))
            {
                throw new Exception('Missing required parameter stripeDiscount in Yotta Pay API response.');
            }
            if (!array_key_exists('defaultReferralAmount', $response_data))
            {
                throw new Exception('Missing required parameter defaultReferralAmount in Yotta Pay API response.');
            }
            if (!array_key_exists('defaultReferralLink', $response_data))
            {
                throw new Exception('Missing required parameter defaultReferralLink in Yotta Pay API response.');
            }

            // - Return verified response data
            $result_data = [
                'status' => '1',
                'userdata' => $response_data,
                'error' => ''
            ];

            return $result_data;
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Userdata', 'execute', $e->getMessage());
        }
    }
}
