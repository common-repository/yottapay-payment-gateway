<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay Frontend Loyalty class
 */
class WC_YottaPay_Frontend_Loyalty
{
    /**
     * Add loyalty button to order page
     */
    public static function add_data_to_order($order)
    {
        try
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_Frontend_Loyalty', 'add_data_to_order', 'Start');
            
            // - Check order
            if (!$order)
            {
                throw new Exception('No order.');
            }

            // - Check request available to execute
            WC_YottaPay_Loyalty_Worker::check_create_deferred_available($order);

            // - Get plugin options
            $options = WC_YottaPay_Options::get_gateway_options();

            // - Add to page
            ?>
            <script type="text/javascript">
                (function ($) {
                    $(document).ready(function() {
                        if (!yottapayCheckCookieExist('yottapay_loyalty_month_popup')) {
                            var btnCreateDeferred = $('#btnYottaPayCreateDeferred');
                            btnCreateDeferred.removeAttr('onclick');
                            btnCreateDeferred.attr('onClick', 'yottapayCreateDeferred(' + <?php echo $order->get_id() ?> + ');');
                            $('#yottapayLoyaltyPopup').show();
                        }
                    });
                })(jQuery);
            </script>
            <div id="yottapayLoyaltyPopup" style="display: none; margin-top: 40px; margin-bottom: 40px; margin-left: auto; margin-right: auto; max-width: 580px; padding: 1em 2em 3em 2em; border-radius: 6px; background: #00a0ff; color: white;">
	            <div>
                    <div style="float: right; width: 60px; text-align: right;">
                        <a id="btnYottaPaySetLoyaltyMonthCookie" href="#" style="text-decoration: none; font-weight: 800; color: white;" onclick="yottapaySetLoyaltyMonthCookie();">
                            <strong>&times;</strong>
                        </a>
                    </div>
                    <div>
                    </div>
                </div>
                <br>
                <div style="padding-left: 1em; padding-right: 1em; text-align: center;">
                    <div style="padding-top: 5px; font-weight: 400; color: white;">
                        <div style="font-weight: 800; max-width: 372px; margin-left: auto; margin-right: auto;">You have earned <?php echo($options['loyalty_bonus'])?>&#37; of your order as loyalty points to use for your next purchase with us.</div>
                        <br>
                        <div style="margin-bottom: 5px;">To do so you will need to download Yotta Pay&#174; app from the link below.</div>
                        <div style="margin-bottom: 5px;">Your loyalty points will be automatically applied.</div>
                        <div style="margin-bottom: 5px;">Use the app to pay for the next order.</div>
                        <br>
                    </div>
                    <div>
                        <a id="btnYottaPayCreateDeferred" href="javascript:void(0)" style="text-decoration: none; color: black; font-weight: 800;" onclick="">
			                <div style="padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px; margin-left: auto; margin-right: auto; max-width: 300px; border-radius: 20px; background-color: white;">
                                Download your loyalty points now
                            </div>
		                </a>
                    </div>
                </div>
            </div>
            <?php

            // - Return success
            return true;
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_Frontend_Loyalty', 'add_data_to_order', $e->getMessage());

            // - Return failed
            return false;
        }
        finally
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_Frontend_Loyalty', 'add_data_to_order', 'End');
        }
    }

    /**
     * Add loyalty button to email
     */
    public static function add_data_to_email($order, $email)
    {
        try
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_Frontend_Loyalty', 'add_data_to_email', 'Start');
            
            // - Check for email type
            if ($email->id != 'customer_processing_order' &&
                $email->id != 'customer_completed_order' &&
                $email->id != 'customer_invoice')
            {
                WC_YottaPay_Logger::log('info', 'WC_YottaPay_Frontend_Loyalty', 'add_data_to_email', 'Incorrect email type', $email->id);                
                return false;
            }

            // - Add deferred points information to email
            WC_YottaPay_Frontend_Loyalty::add_deferred_points_email_data($order);

            // - Add referral information to email
            WC_YottaPay_Frontend_Loyalty::add_referral_email_data();

            // - Return success
            return true;
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_Frontend_Loyalty', 'add_data_to_email', $e->getMessage());
            
            // - Return failed
            return false;
        }
        finally
        {
            WC_YottaPay_Logger::log('info', 'WC_YottaPay_Frontend_Loyalty', 'add_data_to_email', 'End');
        }
    }
        
    //---------------------------------------------------
    //  PRIVATE
    //---------------------------------------------------

    /**
     * Add deferred points information to email
     */
    private static function add_deferred_points_email_data($order)
    {
        // - Request to API
        $result = WC_YottaPay_API_Loyalty::execute($order);

        // - Get link from response
        $result_data = $result['data'];

        // - Check result status
        if ($result_data['status'] == '1')
        {
            // -- Get link from response
            $link = $result_data['link'];

            // -- Get plugin options
            $options = WC_YottaPay_Options::get_gateway_options();

            // -- Add info
            echo '
                <div id="yottapayLoyaltyPopup" style="margin-top: 40px; margin-bottom: 40px; margin-left: auto; margin-right: auto; max-width: 500px; padding: 2em 2em 2em 2em; border-radius: 6px; background: #00a0ff; color: white;">
                    <div style="padding-left: 1em; padding-right: 1em; text-align: center;">
                        <div style="font-weight: 400; color: white;">
                            <div style="font-weight: 800; max-width: 372px; margin-left: auto; margin-right: auto;">
                                You have earned ' . $options['loyalty_bonus'] . '&#37; of your order as loyalty points to use for your next purchase with us.
                            </div>
                            <br>
                            <div style="margin-bottom: 5px;">To do so you will need to download Yotta Pay&#174; app from the link below.</div>
                            <div style="margin-bottom: 5px;">Your loyalty points will be automatically applied.</div>
                            <div style="margin-bottom: 5px;">Use the app to pay for the next order.</div>
                            <br>
                        </div>
                        <div>
                            <a id="btnYottaPayCreateDeferred" href="' . $link . '" style="text-decoration: none; color: black; font-weight: 800;">
                                <div style="padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px; margin-left: auto; margin-right: auto; max-width: 300px; border-radius: 20px; background-color: white;">
                                    Download your loyalty points now
                                </div>
                            </a>
                        </div>
                    </div>
                </div>';
        }
    }

    /**
     * Add referral information to email
     */
    private static function add_referral_email_data()
    {
        // - Trading name
        $referral_trading_name = WC_YottaPay_Options_Worker::get_yottapay_meta_data_option(WC_YottaPay_Options::YOTTAPAY_META_DATA_TRADING_NAME);
        // - Link
        $referral_points_link = WC_YottaPay_Options_Worker::get_yottapay_meta_data_option(WC_YottaPay_Options::YOTTAPAY_META_DATA_DEFAULT_REFERRAL_LINK);
        // - Amount
        $referral_points_amount_string = WC_YottaPay_Options_Worker::get_yottapay_meta_data_option(WC_YottaPay_Options::YOTTAPAY_META_DATA_DEFAULT_REFERRAL_AMOUNT);
        
        // - Check option values
        if ($referral_points_amount_string != '' && $referral_points_amount_string != '-1' && $referral_points_amount_string != '0')
        {
            $referral_points_amount = intval($referral_points_amount_string);
            $referral_points_amount = floatval($referral_points_amount) / 100;
            $referral_points_amount = floatval(number_format(round($referral_points_amount, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
        }
        else
        {
            $referral_points_amount = 0;
        }

        // - Add referral info
        if ($referral_trading_name != '' &&
            $referral_points_link != '' &&
            $referral_points_amount > 0)
        {
            echo '
                <div id="yottapayReferral" style="margin-top: 40px; margin-bottom: 40px; margin-left: auto; margin-right: auto; max-width: 500px; padding: 2em 2em 2em 2em; border-radius: 6px; background: #00a0ff; color: white;">
                    <div style="padding-left: 1em; padding-right: 1em; text-align: center;">
                        <div style="font-weight: 400;">
                            <div style="font-weight: 800; font-size: large; max-width: 372px; margin-left: auto; margin-right: auto;">
                                &#163;' . $referral_points_amount . ' for your friends and family on us.
                            </div>
                            <br>
                            <div style="margin-bottom: 5px; font-size: smaller;">Use message template and link below to give a gift to your network</div>
                            <br>
                            <div style="margin-bottom: 5px;">Just shopped with ' . $referral_trading_name . ' - they are amazing. They offer a welcome voucher of &#163;' . $referral_points_amount . ' - here is yours. Follow the link and use Yotta Pay at the checkout</div>
                            <br>
                            <div style="margin-bottom: 5px;">
                                <a id="btnYottapayReferral" href="' . $referral_points_link . '" style=" color: white;">' . $referral_points_link . '</a>
                            </div>
                            <br>
                            <div style="margin-bottom: 5px;">Thanks a lot</div>
                        </div>
                    </div>
                </div>';
        }
    }
}
