<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay API Authorize Webhook class
 */
class WC_YottaPay_API_Authorize_Webhook
{
    /**
     * Process request
     */
    public static function execute()
    {
        try
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Authorize_Webhook', 'execute', 'Start');

            // - Get data from request
            $request_data = WC_YottaPay_API_Worker::get_request_data(false);

            // - Check required fields
            self::check_request_data($request_data);

            // - Reload client userdata
            WC_YottaPay_Userdata_Worker::reload_client_userdata();

            // - Set checkout_description type
            WC_YottaPay_Options_Worker::set_checkout_description_type();

            // - Return success
            $result_data = 'OK';
            $result_http_status_code = 200;
        }
        catch (Exception $e)
        {
            // - Log
            $exception_message = ('Error WC_YottaPay_API_Authorize_Webhook [v. '. YOTTAPAY_PLUGIN_VERSION . ']: ' . $e->getMessage());

            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Authorize_Webhook', 'execute', $exception_message);

            // - Set message and status code
            $result_data = [
                'message' => $exception_message,
                'trace' => null
            ];
            $result_http_status_code = 500;
        }
        finally
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Authorize_Webhook', 'execute', 'End');
            
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
     * Check required fields
     */
    private static function check_request_data($request_data)
    {
        // - Verify required keys
        if (!array_key_exists('userId', $request_data) || !array_key_exists('userToken', $request_data))
        {
            throw new Exception('Missing required parameters in request');
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
            throw new Exception('Invalid request data');
        }

        // - Verify requested userToken
        if (sanitize_text_field($request_data['userToken']) == '')
        {
            throw new Exception('Empty userToken');
        }

        // - Set user_token option        
        if (WC_YottaPay_Options::update_gateway_option('user_token', sanitize_text_field($request_data['userToken'])) == false)
        {
            throw new Exception('Failed to set user_token parameter');
        }

        // - Enable payment method
        if (!(boolval($options['enabled']) && wc_strtolower($options['enabled']) != 'no'))
        {
            if (WC_YottaPay_Options::update_gateway_option('enabled', 'yes') == false)
            {
                throw new Exception('Failed to set enabled parameter');
            }
        }        
    }
}
