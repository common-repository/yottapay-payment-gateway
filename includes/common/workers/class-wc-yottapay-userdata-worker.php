<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay Userdata Worker class
 */
class WC_YottaPay_Userdata_Worker
{
    /**
     * Execute request to reload client userdata and save updated option
     */
    public static function reload_client_userdata()
    {
        try
        {
            // - Make request to Yotta Pay API
            $result = WC_YottaPay_API_Userdata::execute();

            // - Get result data
            $result_data = $result['data'];

            // - Check result status
            if ($result_data['status'] == '0')
            {
                throw new Exception($result_data['error']);
            }
        
            // - Build meta_data_option_string
            $plugin_settings_meta_data = WC_YottaPay_Options_Worker::build_yottapay_meta_data_option_string($result_data['userdata']);

            // - Get source yottapay_meta_data from options
            $options = WC_YottaPay_Options::get_gateway_options();
            $source_plugin_settings_meta_data = $options['yottapay_meta_data'];

            // - Set yottapay_meta_data
            if ($plugin_settings_meta_data != $source_plugin_settings_meta_data)
            {
                if (WC_YottaPay_Options::update_gateway_option('yottapay_meta_data', $plugin_settings_meta_data) == false)
                {
                    throw new Exception('Failed to update yottapay_meta_data parameter');
                }
            }

            return true;
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_Userdata_Worker', 'reload_client_userdata', $e->getMessage());

            return false;
        }
    }
}
