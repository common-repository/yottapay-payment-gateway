<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay API Authorize class
 */
class WC_YottaPay_API_Authorize
{
    /**
     * Execute authorize request to Yotta Pay API
     * Return result containing URL to redirect to Yotta Pay authorize page
     */
    public static function execute()
    {
        try
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Authorize', 'execute', 'Start');
  
            // - Init default result and http status code
            $result_data = [
                'status' => '0',
                'link' => '',
                'error' => 'Request failed.'
            ];
            $result_http_status_code = 200;

            // - Check session context
            if (!current_user_can('manage_options'))
            {
                throw new Exception('Requested by a non-admin user');
            }

            // - Create and save user_id
            $user_id = WC_YottaPay_Common::get_uuid();
            if (WC_YottaPay_Options::update_gateway_option('user_id', $user_id) == false)
            {
                throw new Exception('Failed to create user_id parameter');
            }

            // - Get request body 
            $request_body = self::get_request_body($user_id);

            // - Make request
            $response_data = WC_YottaPay_API_Worker::make_post_request(WC_YottaPay_API_Endpoints::AUTH_NEW, $request_body);
            
            // - Process response
            $result_data = self::process_response($response_data);   
        }
        catch (WC_YottaPay_Exception $e)
        {
            // - Show notification to customer
            wc_add_notice($e->getMessage(), 'error');

            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Authorize', 'execute', $e->getMessage());
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Authorize', 'execute', $e->getMessage());
        }
        finally
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Authorize', 'execute', 'End');
            
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
    private static function get_request_body($user_id)
    {
        $body = [
            'webhookUrl' => (get_site_url() . WC_YottaPay_API_Webhooks::AUTH_NEW),
            'redirectUrl' => admin_url('admin.php?page=wc-settings&tab=checkout&section=yottapay&authorized=1'),
            'userId' => $user_id,
        ];

        return $body;
    }

    /**
     * Return result of response processing
     */
    private static function process_response($response_data)
    {
        // - Check required keys
        if (!array_key_exists('authUrl', $response_data))
        {
            throw new Exception('Missing required parameters in response.');
        }

        // - Result data
        $result = array(
            'status' => '1',
            'link' => sanitize_text_field($response_data['authUrl']),
            'error' => ''
        );

        return $result;
    }
}
