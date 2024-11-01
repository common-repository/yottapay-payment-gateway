<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC_YottaPay_Options class
 */
class WC_YottaPay_Options
{
    //Option string: tradingName|||stripeDiscount|||defaultReferralAmount|||defaultReferralLink
    const YOTTAPAY_META_DATA_TRADING_NAME = 0;
    const YOTTAPAY_META_DATA_STRIPE_DISCOUNT = 1;
    const YOTTAPAY_META_DATA_DEFAULT_REFERRAL_AMOUNT = 2;
    const YOTTAPAY_META_DATA_DEFAULT_REFERRAL_LINK = 3;

    /**
     * Return list of plugin option values
     */
    public static function get_gateway_options()
    {
        // - Get gateway instance
        $gateway = WC_Gateway_YottaPay::get_instance();

        // - Get options from gateway
        $options = [
            'yottapay_meta_data' => $gateway->get_option('yottapay_meta_data', ''),
            'user_id' => $gateway->get_option('user_id', ''),
            'user_token' => $gateway->get_option('user_token', ''),
            'enabled' => $gateway->get_option('enabled', 'no'),
            'checkout_description_type' => $gateway->get_option('checkout_description_type', 'option_mobile_banking'),
            'gateway_discount' => $gateway->get_option('gateway_discount', 'option_0'),
            'gtm_id' => $gateway->get_option('gtm_id', ''),
            'loyalty_bonus' => $gateway->get_option('loyalty_bonus', 'option_0'),
            'loyalty_max_bonus' => $gateway->get_option('loyalty_max_bonus', '0'),
            'loyalty_start_period' => $gateway->get_option('loyalty_start_period', ''),
        ];
        
        // - Return loyalty_bonus as integer
        if ($options['loyalty_bonus'] == 'option_0' || $options['loyalty_bonus'] == 'default')
        {
            $options['loyalty_bonus'] = 0;
        }
        else
        {
            $options['loyalty_bonus'] = intval(explode('_', $options['loyalty_bonus'])[1]);
        }

        // - Return gateway_discount as integer
        if ($options['gateway_discount'] == 'option_0' || $options['gateway_discount'] == 'default')
        {
            $options['gateway_discount'] = 0;
        }
        else
        {
            $options['gateway_discount'] = intval(explode('_', $options['gateway_discount'])[1]);
        }

        // - Return options list
        return $options;
    }

    /**
     * Validate option values before save
     */
    public static function verify_gateway_option_values($option_name, $old_value, $value)
    {
        if ($option_name == 'woocommerce_yottapay_settings')
        {
            try
            {
                WC_YottaPay_Logger::log('info', 'WC_YottaPay_Options', 'verify_gateway_option_values', 'Start');

                // - Log changed values
                WC_YottaPay_Logger::log('info', 'WC_YottaPay_Options', 'verify_gateway_option_values', 'checkout_description_type', $old_value['checkout_description_type'] . ' | ' . $value['checkout_description_type']);
                WC_YottaPay_Logger::log('info', 'WC_YottaPay_Options', 'verify_gateway_option_values', 'gtm_id', $old_value['gtm_id'] . ' | ' . $value['gtm_id']);
                WC_YottaPay_Logger::log('info', 'WC_YottaPay_Options', 'verify_gateway_option_values', 'yottapay_meta_data', $old_value['yottapay_meta_data'] . ' | ' . $value['yottapay_meta_data']);
                WC_YottaPay_Logger::log('info', 'WC_YottaPay_Options', 'verify_gateway_option_values', 'loyalty_bonus', $old_value['loyalty_bonus'] . ' | ' . $value['loyalty_bonus']);
                WC_YottaPay_Logger::log('info', 'WC_YottaPay_Options', 'verify_gateway_option_values', 'loyalty_max_bonus', $old_value['loyalty_max_bonus'] . ' | ' . $value['loyalty_max_bonus']);
                WC_YottaPay_Logger::log('info', 'WC_YottaPay_Options', 'verify_gateway_option_values', 'loyalty_start_period', $old_value['loyalty_start_period'] . ' | ' . $value['loyalty_start_period']);

                // - Check GTM ID value
                $valid_gtm_id = WC_YottaPay_Options_Worker::check_for_valid_option_value_gtm_id($old_value, $value);
                if (!$valid_gtm_id)
                {
                    WC_YottaPay_Options::update_gateway_option('gtm_id', '');
                }

                // - Check loyalty rate value
                $valid_loyalty_rate = WC_YottaPay_Options_Worker::check_for_valid_option_value_loyalty_rate($old_value, $value);
                if (!$valid_loyalty_rate)
                {
                    WC_YottaPay_Options::update_gateway_option('loyalty_bonus', 'option_0');
                }

                // - Check loyalty upper limit value
                $valid_loyalty_upper_limit = WC_YottaPay_Options_Worker::check_for_valid_option_value_loyalty_upper_limit($old_value, $value);
                if (!$valid_loyalty_upper_limit)
                {
                    WC_YottaPay_Options::update_gateway_option('loyalty_max_bonus', '0');
                }

                // - Check loyalty start period value
                $valid_loyalty_start_period = WC_YottaPay_Options_Worker::check_for_valid_option_value_loyalty_start_period($old_value, $value);
                if (!$valid_loyalty_start_period)
                {
                    WC_YottaPay_Options::update_gateway_option('loyalty_start_period', '');
                }
            }
            catch (Exception $e)
            {
                WC_YottaPay_Logger::log('warning', 'WC_YottaPay_Options', 'verify_gateway_option_values', $e->getMessage());
            }
            finally
            {
                WC_YottaPay_Logger::log('info', 'WC_YottaPay_Options', 'verify_gateway_option_values', 'End');
            }
        }
    }

    /**
     * Update option value
     */
    public static function update_gateway_option($option_name, $value)
    {
        // - Get gateway instance
        $gateway = WC_Gateway_YottaPay::get_instance();

        // - Update option
        return $gateway->update_option($option_name, $value);
    }

    /**
     * Return Gateway Options Form Fields
     */
    public static function get_form_fields()
    {
        return array(
            'user_id' => array(
                'title' => '',
                'type' => 'password',
                'default' => '',
                'css' => 'display: none;',
            ),            
            'user_token' => array(
                'title' => '',
                'type' => 'password',
                'default' => '',
                'css' => 'display: none;',
            ),
            'enabled' => array(
                'title' => 'Enable / Disable',
                'label' => 'Enable Yotta Pay',
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no',
            ),
            'checkout_description_type' => array(
			    'title' => 'Checkout Description',
			    'type' => 'select',
                'description' => 'Select a description of the payment method on the checkout page.',
			    'default' => 'option_mobile_banking',
			    'desc_tip' => true,
			    'options' => array(
                    'option_mobile_banking' => 'Mobile Banking',
				    'option_card_payment' => 'Mobile Banking & Card Payment',
			    ),
		    ),
            'gateway_discount' => array(
			    'title' => 'Gateway Discount',
			    'label' => 'Type',
			    'type' => 'select',
                'description' => 'Add a total amount discount to every order placed through the Faster Checkout.',
			    'default' => 'option_0',
			    'desc_tip' => true,
			    'options' => array(
                    'option_0' => 'Disabled',
				    'option_1' => '1%', 'option_2' => '2%', 'option_3' => '3%',
                    'option_4' => '4%', 'option_5' => '5%', 'option_6' => '6%',
                    'option_7' => '7%', 'option_8' => '8%', 'option_9' => '9%',
                    'option_10' => '10%', 'option_11' => '11%', 'option_12' => '12%',
                    'option_13' => '13%', 'option_14' => '14%', 'option_15' => '15%',
                    'option_16' => '16%', 'option_17' => '17%', 'option_18' => '18%',
                    'option_19' => '19%', 'option_20' => '20%', 'option_21' => '21%',
                    'option_22' => '22%', 'option_23' => '23%', 'option_24' => '24%',
                    'option_25' => '25%', 'option_26' => '26%', 'option_27' => '27%',
                    'option_28' => '28%', 'option_29' => '29%', 'option_30' => '30%',
                    'option_31' => '31%', 'option_32' => '32%', 'option_33' => '33%',
                    'option_34' => '34%', 'option_35' => '35%', 'option_36' => '36%',
                    'option_37' => '37%', 'option_38' => '38%', 'option_39' => '39%',
                    'option_40' => '40%', 'option_41' => '41%', 'option_42' => '42%',
                    'option_43' => '43%', 'option_44' => '44%', 'option_45' => '45%',
                    'option_46' => '46%', 'option_47' => '47%', 'option_48' => '48%',
                    'option_49' => '49%', 'option_50' => '50%', 'option_51' => '51%',
                    'option_52' => '52%', 'option_53' => '53%', 'option_54' => '54%',
                    'option_55' => '55%', 'option_56' => '56%', 'option_57' => '57%',
                    'option_58' => '58%', 'option_59' => '59%', 'option_60' => '60%',
                    'option_61' => '61%', 'option_62' => '62%', 'option_63' => '63%',
                    'option_64' => '64%', 'option_65' => '65%', 'option_66' => '66%',
                    'option_67' => '67%', 'option_68' => '68%', 'option_69' => '69%',
                    'option_70' => '70%', 'option_71' => '71%', 'option_72' => '72%',
                    'option_73' => '73%', 'option_74' => '74%', 'option_75' => '75%',
                    'option_76' => '76%', 'option_77' => '77%', 'option_78' => '78%',
                    'option_79' => '79%', 'option_80' => '80%', 'option_81' => '81%',
                    'option_82' => '82%', 'option_83' => '83%', 'option_84' => '84%',
                    'option_85' => '85%', 'option_86' => '86%', 'option_87' => '87%',
                    'option_88' => '88%', 'option_89' => '89%', 'option_90' => '90%',
                    'option_91' => '91%', 'option_92' => '92%', 'option_93' => '93%',
                    'option_94' => '94%', 'option_95' => '95%', 'option_96' => '96%',
                    'option_97' => '97%', 'option_98' => '98%', 'option_99' => '99%',
			    ),
		    ),
            'gtm_id' => array(
                'title' => 'Google Tag Manager Id',
                'type' => 'text',
                'description' => 'Set your tag manager here to track conversions and other client behavior for your'
                                    . 'Facebook, Instagram, Google, direct, and organic traffic acquisition channels.'
                                    . '<br><br>How to get:'
                                    . '<br>https://www.yottapay.co.uk/faq-questions/how-to-set-up-a-tag-manager-for-user-tracking',
                'desc_tip' => true,
            ),
            'loyalty_bonus' => array(
			    'title' => 'Welcome Loyalty Rate',
			    'label' => 'Type',
			    'type' => 'select',
                'description' => 'You will be offering this much loyalty points'
                                    . ' to every customer since the "Loyalty started" date.'
                                    . '<br>'
                                    . ' The retrospective loyalty points reward'
                                    . ' will be granted only once, for one order.',
			    'default' => 'option_0',
			    'desc_tip' => true,
			    'options' => array(
                    'option_0' => 'Disabled',
				    'option_1' => '1%', 'option_2' => '2%', 'option_3' => '3%',
                    'option_4' => '4%', 'option_5' => '5%', 'option_6' => '6%',
                    'option_7' => '7%', 'option_8' => '8%', 'option_9' => '9%',
                    'option_10' => '10%', 'option_11' => '11%', 'option_12' => '12%',
                    'option_13' => '13%', 'option_14' => '14%', 'option_15' => '15%',
                    'option_16' => '16%', 'option_17' => '17%', 'option_18' => '18%',
                    'option_19' => '19%', 'option_20' => '20%', 'option_21' => '21%',
                    'option_22' => '22%', 'option_23' => '23%', 'option_24' => '24%',
                    'option_25' => '25%', 'option_26' => '26%', 'option_27' => '27%',
                    'option_28' => '28%', 'option_29' => '29%', 'option_30' => '30%',
                    'option_31' => '31%', 'option_32' => '32%', 'option_33' => '33%',
                    'option_34' => '34%', 'option_35' => '35%', 'option_36' => '36%',
                    'option_37' => '37%', 'option_38' => '38%', 'option_39' => '39%',
                    'option_40' => '40%', 'option_41' => '41%', 'option_42' => '42%',
                    'option_43' => '43%', 'option_44' => '44%', 'option_45' => '45%',
                    'option_46' => '46%', 'option_47' => '47%', 'option_48' => '48%',
                    'option_49' => '49%', 'option_50' => '50%', 'option_51' => '51%',
                    'option_52' => '52%', 'option_53' => '53%', 'option_54' => '54%',
                    'option_55' => '55%', 'option_56' => '56%', 'option_57' => '57%',
                    'option_58' => '58%', 'option_59' => '59%', 'option_60' => '60%',
                    'option_61' => '61%', 'option_62' => '62%', 'option_63' => '63%',
                    'option_64' => '64%', 'option_65' => '65%', 'option_66' => '66%',
                    'option_67' => '67%', 'option_68' => '68%', 'option_69' => '69%',
                    'option_70' => '70%', 'option_71' => '71%', 'option_72' => '72%',
                    'option_73' => '73%', 'option_74' => '74%', 'option_75' => '75%',
                    'option_76' => '76%', 'option_77' => '77%', 'option_78' => '78%',
                    'option_79' => '79%', 'option_80' => '80%', 'option_81' => '81%',
                    'option_82' => '82%', 'option_83' => '83%', 'option_84' => '84%',
                    'option_85' => '85%', 'option_86' => '86%', 'option_87' => '87%',
                    'option_88' => '88%', 'option_89' => '89%', 'option_90' => '90%',
                    'option_91' => '91%', 'option_92' => '92%', 'option_93' => '93%',
                    'option_94' => '94%', 'option_95' => '95%', 'option_96' => '96%',
                    'option_97' => '97%', 'option_98' => '98%', 'option_99' => '99%',
                    'option_100' => '100%',
			    ),
		    ),
            'loyalty_max_bonus' => array(
                'title' => 'Welcome Loyalty Upper Limit',
                'type' => 'text',
                'description' => 'Use this only to limit welcome loyalty rewards to '
                                    . 'unusually high value orders in the past to keep'
                                    . ' your economics sustainable.'
                                    . '<br>'
                                    . 'If you do not have such orders,'
                                    . ' simply leave it to zero.',
                'default' => '0',
                'desc_tip' => true,
            ),
            'loyalty_start_period' => array(
                'title' => 'Loyalty Started (DD-MM-YYYY)',
                'type' => 'text',
                'description' => 'Your customers will be offered to sign up to your'
                                    . ' Loyalty program and enjoy welcome loyalty'
                                    . ' for any ONE order during the period since this date.'
                                    . '<br>'
                                    . ' We recommend to set it up at least one year back.',
                'default' => '',
                'desc_tip' => true,
            ),
            'yottapay_meta_data' => array(
                'title' => '',
                'type' => 'text',
                'desc_tip' => true,
                'default' => '',
                'css' => 'display: none;',
            ),
        );
    }
}
