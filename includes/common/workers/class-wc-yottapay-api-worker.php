<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay API Worker class
 */
class WC_YottaPay_API_Worker
{
    //Request headers
    private static $x_client_key = 'efeb33a3-18d7-4c8f-9aeb-fe90f4552c5fWOO';
    private static $x_client_secret = 'a0c2c931-b897-4cbd-b056-edd239677763WOO';

    /**
     * Make GET request to Yotta Pay API
     */
    public static function make_get_request($request_endpoint, $timeout = 20, $need_log = true)
    {
        // - Request URL
        $request_url = WC_YottaPay_API_Endpoints::BASE . $request_endpoint;

        // - Log
        if ($need_log)
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Worker', 'make_get_request', 'request_url', print_r($request_url, TRUE));
        }

        // - Request args
        $request_args = [
            'timeout' => $timeout,
            'headers' => [
                'X-CLIENT-KEY' => self::$x_client_key,
                'X-CLIENT-SECRET' => self::$x_client_secret
            ],
        ];

        // - Make GET request
        $response = wp_remote_get($request_url, $request_args);

        // - Return response data or throw exception
        $response_data = self::validate_response_status($response, $need_log);

        return $response_data;
    }

    /**
     * Make POST request to Yotta Pay API
     */
    public static function make_post_request($request_endpoint, $request_body, $timeout = 20, $need_log = true)
    {
        // - Request URL
        $request_url = WC_YottaPay_API_Endpoints::BASE . $request_endpoint;

        // - Request body
        $json_request_body = json_encode($request_body);

        // - Log
        if ($need_log)
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Worker', 'make_post_request', 'request_url', print_r($request_url, TRUE));

            // - Clear userAccessToken and userId from log
            $temp_request_body = $request_body;
            if (array_key_exists('userAccessToken', $temp_request_body))
            {
                $temp_request_body['userAccessToken'] = '...' . substr($temp_request_body['userAccessToken'], -8);
            }
            if (array_key_exists('userId', $temp_request_body))
            {
                $temp_request_body['userId'] = '...' . substr($temp_request_body['userId'], -8);
            }

            // - Log request_body
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Worker', 'make_post_request', 'request_body', print_r($temp_request_body, TRUE));
        }

        // - Request args
        $request_args = [
            'timeout' => $timeout,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-CLIENT-KEY' => self::$x_client_key,
                'X-CLIENT-SECRET' => self::$x_client_secret
            ],
            'body' => $json_request_body
        ];

        // - Make POST request
        $response = wp_remote_post($request_url, $request_args);

        // - Return response data or throw exception
        $response_data = self::validate_response_status($response, $need_log);

        return $response_data;
    }

    /**
     * Get data from webhook request
     * Return decoded json or throw exception
     */
    public static function get_request_data($need_log = true)
    {
        // - Read request data
        $request_data = file_get_contents('php://input');
        if (!$request_data)
        {
            throw new Exception('No request data');
        }

        // - Log
        if ($need_log)
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Worker', 'get_request_data', 'request_data', print_r($request_data, TRUE));
        }

        // - Decode JSON
        $json_request_data = json_decode($request_data, true);

        if (!$request_data)
        {
            throw new Exception('No correct request data');
        }

        return $json_request_data;
    }
    
    //---------------------------------------------------
    //  PRIVATE
    //---------------------------------------------------

    /**
     * Validate response status
     * Return decoded json or throw exception
     */
    private static function validate_response_status($response, $need_log = true)
    {
        // - Check is_wp_error
        if (is_wp_error($response))
        {
            $error_message = $response->get_error_message();
            throw new Exception($error_message);
        }

        // - Check response status code
        if (wp_remote_retrieve_response_code($response) !== 200)
        {
            // -- Log response data
            if ($need_log)
            {
                try
                {
                    $response_body = wp_remote_retrieve_body($response);

                    if ($response_body != '')
                    {
                        $response_data = json_decode($response_body, true);

                        WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Worker', 'validate_response_status', 'error response data', print_r($response_data, TRUE));
                    }
                }
                catch (Exception $e)
                {
                    WC_YottaPay_Logger::log('warning', 'WC_YottaPay_API_Worker', 'validate_response_status', 'Failed to receive error response data');
                }
            }

            // -- Throw exception
            throw new Exception(
                'Yotta Pay API response code: '
                . wp_remote_retrieve_response_code($response)
                . ' | '
                . $response['http_response']->get_response_object()->url
            );
        }

        // - Get response data
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        // - Log response data
        if ($need_log)
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_API_Worker', 'validate_response_status', 'response data', print_r($response_data, TRUE));
        }

        return $response_data;
    }
}
