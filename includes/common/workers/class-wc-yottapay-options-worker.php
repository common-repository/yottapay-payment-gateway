<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay Options Worker class
 */
class WC_YottaPay_Options_Worker
{
    /**
     * Check value for according Google Tag Manager ID mask
     */
    public static function check_for_valid_option_value_gtm_id($old_value, $value)
    {        
        // - Check new value is empty
        if (strlen($value['gtm_id']) == 0)
        {
            return true;
        }

        // - Check according to pattern
        $pattern = '/^GTM-[A-Z0-9]{1,8}$/';
        $preg_result = preg_match($pattern, $value['gtm_id']);
        if (boolval($preg_result) && wc_strtolower($preg_result) != 'false')
        {
            return true;
        }
        else
        {
            echo '<div id="yotta-pay-warning-loyalty-upper-limit" class="notice notice-warning is-dismissible">
                    <p>Please enter a valid Google Tag Manager Id or leave it blank.</p>
                </div>';

            return false;
        }        
    }

    /**
     * Check loyalty rate option
     */
    public static function check_for_valid_option_value_loyalty_rate($old_value, $value)
    {
        try
        {
            // - Disable option
            if ($value['loyalty_bonus'] == 'option_0' ||
                $value['loyalty_bonus'] == 'default')
            {
                return true;
            }

            // - Next verification if changed default value only
            if ($old_value['loyalty_bonus'] != 'option_0' &&
                $old_value['loyalty_bonus'] != 'default')
            {
                return true;
            }

            // - Make request
            $response_data = WC_YottaPay_API_Worker::make_get_request(WC_YottaPay_API_Endpoints::LOYALTY_IS_ACTIVE . '/' . $value['user_token']);

            // - Verify required keys
            if (!array_key_exists('result', $response_data))
            {
                throw new Exception('Missing required parameters in Yotta Pay API response.');
            }

            // - Validate result status
            $response_status = sanitize_text_field($response_data['result']);
            if (boolval($response_status) && wc_strtolower($response_status) != 'false')
            {
                return true;
            }
            else            
            {
                echo '<div id="yotta-pay-warning-loyalty-rate" class="notice notice-warning is-dismissible">
                        <p>Please check that the Loyalty program has been enabled in the YottaPay App to enable <b>Loyalty Percentage</b>.</p>
                        <p>Check that <b>YottaPay App > Business > Loyalty program > Enable loyalty program</b>, has been enabled and set accordingly.</p>
                </div>';

                return false;
            }
        }
        catch (Exception $e)
        {
            echo '<div id="yotta-pay-warning-loyalty-rate" class="notice notice-warning is-dismissible">
                    <p>To activate Welcome Loyalty Rate check internet connection.</p>
            </div>';

            return false;
        }
    }

    /**
     * Check loyalty max rate option
     */
    public static function check_for_valid_option_value_loyalty_upper_limit($old_value, $value)
    {
        // - Check value is integer
        $strIntParsedLoyaltyMaxBonus = strval(intval($value['loyalty_max_bonus']));
        $strParsedLoyaltyMaxBonus = strval($value['loyalty_max_bonus']);

        if ($strIntParsedLoyaltyMaxBonus == $strParsedLoyaltyMaxBonus)
        {
            return true;
        }
        else
        {
            echo '<div id="yotta-pay-warning-loyalty-upper-limit" class="notice notice-warning is-dismissible">
                    <p>Welcome Loyalty Upper Limit is integer.</p>
                </div>';

            return false;
        }
    }

    /**
     * Check loyalty start period option
     */
    public static function check_for_valid_option_value_loyalty_start_period($old_value, $value)
    {
        if (strlen($value['loyalty_start_period']) == 0)
        {
            return true;
        }

        // - Check inputed value is DD-MM-YYYY
        $createdFromFormat = DateTime::createFromFormat('d-m-Y', $value['loyalty_start_period']);
        $parsedDate = date_parse($value['loyalty_start_period']);

        if ($createdFromFormat == true && $parsedDate['error_count'] == 0)
        {
            return true;
        }
        else
        {
            echo '<div id="yotta-pay-warning-loyalty-start-period" class="notice notice-warning is-dismissible">
                    <p>Loyalty start date format is DD-MM-YYYY.</p>
                </div>';

            return false;
        }
    }

    /**
     * Check stripe available status and set checkout_description_type option
     */
    public static function set_checkout_description_type()
    {
        try
        {
            // - Get plugin options
            $options = WC_YottaPay_Options::get_gateway_options();

            // - Get stripe discount
            $stripe_discount = WC_YottaPay_Options_Worker::get_yottapay_meta_data_option(WC_YottaPay_Options::YOTTAPAY_META_DATA_STRIPE_DISCOUNT);

            // - If stripe disabled
            if ($stripe_discount == '' || $stripe_discount == '-1')
            {
                if ($options['checkout_description_type'] != 'option_mobile_banking')
                {
                    if (WC_YottaPay_Options::update_gateway_option('checkout_description_type', 'option_mobile_banking') == false)
                    {
                        throw new Exception('Failed to set option checkout_description_type as option_mobile_banking');
                    }
                }
            }
            else
            {
                if ($options['checkout_description_type'] != 'option_card_payment')
                {
                    if (WC_YottaPay_Options::update_gateway_option('checkout_description_type', 'option_card_payment') == false)
                    {
                        throw new Exception('Failed to set option checkout_description_type as option_card_payment');
                    }
                }
            }
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_Options_Worker', 'set_checkout_description_type', $e->getMessage());
        }
    }

    /**
     * Return value for setup to yottapay_meta_data option
     */
    public static function build_yottapay_meta_data_option_string($response_data)
    {
        try
        {
            // - Build option string: tradingName|||stripeDiscount|||defaultReferralAmount|||defaultReferralLink
            $plugin_settings_meta_data = '';
            $plugin_settings_meta_data = $plugin_settings_meta_data . $response_data['tradingName'] . '|||';
            $plugin_settings_meta_data = $plugin_settings_meta_data . $response_data['stripeDiscount'] . '|||';
            $plugin_settings_meta_data = $plugin_settings_meta_data . $response_data['defaultReferralAmount'] . '|||';
            $plugin_settings_meta_data = $plugin_settings_meta_data . $response_data['defaultReferralLink'];

            // - Sanitize text field
            $plugin_settings_meta_data = sanitize_text_field($plugin_settings_meta_data);

            return $plugin_settings_meta_data;
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_Options_Worker', 'build_yottapay_meta_data_option_string', $e->getMessage());
            
            return '';
        }
    }

    /**
     * Return part of yottapay_meta_data option
     */
    public static function get_yottapay_meta_data_option($data_type)
    {
        try
        {
            // - Init default result
            $value = '';

            // - Get plugin options
            $options = WC_YottaPay_Options::get_gateway_options();

            // - Get yottapay_meta_data option value
            $plugin_settings_meta_data = $options['yottapay_meta_data'];

            if ($plugin_settings_meta_data != '')
            {
                $splited_plugin_settings_meta_data = explode('|||', $plugin_settings_meta_data);

                if ($data_type == WC_YottaPay_Options::YOTTAPAY_META_DATA_TRADING_NAME)
                {
                    $value = $splited_plugin_settings_meta_data[WC_YottaPay_Options::YOTTAPAY_META_DATA_TRADING_NAME];
                }
                elseif ($data_type == WC_YottaPay_Options::YOTTAPAY_META_DATA_STRIPE_DISCOUNT)
                {
                    $value = $splited_plugin_settings_meta_data[WC_YottaPay_Options::YOTTAPAY_META_DATA_STRIPE_DISCOUNT];
                }
                elseif ($data_type == WC_YottaPay_Options::YOTTAPAY_META_DATA_DEFAULT_REFERRAL_AMOUNT)
                {
                    $value = $splited_plugin_settings_meta_data[WC_YottaPay_Options::YOTTAPAY_META_DATA_DEFAULT_REFERRAL_AMOUNT];
                }
                elseif ($data_type == WC_YottaPay_Options::YOTTAPAY_META_DATA_DEFAULT_REFERRAL_LINK)
                {
                    $value = $splited_plugin_settings_meta_data[WC_YottaPay_Options::YOTTAPAY_META_DATA_DEFAULT_REFERRAL_LINK];
                }                
            }
            
            return $value;
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_Options_Worker', 'get_yottapay_meta_data_option', $e->getMessage());

            return '';
        }
    }
}
