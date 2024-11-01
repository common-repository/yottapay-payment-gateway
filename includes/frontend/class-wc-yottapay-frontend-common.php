<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay Frontend Common class
 */
class WC_YottaPay_Frontend_Common
{
    /**
     * Print scripts
     */
    public static function print_scripts()
    {
        ?>
            <script src="<?php echo(YOTTAPAY_PLUGIN_URL . 'assets/js/yottapay.js')?>"></script>
			<script src="<?php echo(YOTTAPAY_PLUGIN_URL . 'assets/js/sourcebuster.min.js')?>"></script>
			<script type="text/javascript">
				sbjs.init();
			</script>
		<?php
    }

    /**
     * Admin print script
     */
    public static function admin_print_scripts()
    {
        ?>
            <script src="<?php echo(YOTTAPAY_PLUGIN_URL . 'assets/js/yottapay-admin.js')?>"></script>
		<?php
    }

    /**
     * Add Settings link
     */
    public static function add_action_links($actions)
    {
        $plugin_settings_page_url = admin_url('admin.php?page=wc-settings&tab=checkout&section=yottapay');

        $new_links = ['<a href="' . $plugin_settings_page_url . '">Settings</a>'];

        $actions = array_merge($actions, $new_links);

        return $actions;
    }

    /**
     * Add Guideline and API docs links
     */
    public static function custom_plugin_row_meta($links, $file)
    {
        if (strpos($file, 'yottapay-payment-gateway.php') !== false)
        {
            $guideline_link = 'https://www.yottapay.co.uk/faq-questions/how-to-add-online-checkout-woo';
            $docs_link = 'https://apiconnect.yottapay.co.uk/redoc';

            $new_links = [
                '<a href="' . $guideline_link . '" target="_blank">Guideline</a>',
                '<a href="' . $docs_link . '" target="_blank">API docs</a>',
            ];

            $links = array_merge($links, $new_links);
        }

        return $links;
    }

    /**
     * Return payment method title
     */
    public static function get_payment_method_title()
    {
        $title = 'Yotta Pay';
        
        return $title;
    }

    /**
     * Return payment method description
     */
    public static function get_payment_method_description()
    {
        return sprintf(__(
            '<div class="yottapay-description">'
            . 'Offer your customers the fastest and the most secure way to pay for your orders.'
            . '<br>'
            . 'Zero fraud, no chargebacks, instant payout to your UK bank account.'
            . '<br>'
            . 'Use our Loyalty program to ensure your customers return to you.'
            . '<br>'
            . 'Let them invite their friends and family under embedded referral scheme.'
            . '<br>'
            . 'Full details <a href="%s">here</a>.'
            . '<br>'
            . '<br>'
            . 'You need to set up the ongoing parameters for loyalty and referral scheme in your merchant Yotta Pay app.'
            . '<br>'
            . 'To market it among your customers you need to set up a welcome loyalty scheme below.'
            . '<br>'
            . 'It will be offered to all of your customers who completed orders since "Loyalty Started" date below.'
            . '<br>'
            . 'Normally we recommend to set up "Welcome loyalty rate" the same as you set it up in the app.'
            . '<br>'
            . 'You can also limit the value of it by setting "Welcome loyalty upper limit"'
            . 'to ensure you don&apos;t reward too much to your existing customers.'
            . '</div>'
        , 'yottapay-payment-gateway'), 'https://www.yottapay.co.uk/online-checkout');
    }

    /**
     * Return payment method frontend title
     */
    public static function get_payment_method_frontend_title()
    {
        $title = 'Faster Checkout&#8482;';
        
        return $title;
    }

    /**
     * Return payment method frontend description
     */
    public static function get_payment_method_frontend_description($option_checkout_description_type)
    {
        if ($option_checkout_description_type == 'option_card_payment')
        {
            return sprintf(__(
                    'Pay your way.'
                    . '<br>Use UK mobile banking (no card details required), ApplePay/GooglePay, or your favourite card.'
                    . '<br>Powered by Yotta Pay &#174; and Stripe.'
                ,'yottapay-payment-gateway')
            );
        }
        else
        {
            return sprintf(__(
                    'Bank transfers made easy.'
                    . '<br>One-click payment with your UK mobile banking. No card details required.'
                    . '<br>Connect to your bank and complete your purchase instantly!'
                    . '<br>Powered by Yotta Pay &#174;.'
                ,'yottapay-payment-gateway')
            );
        }
    }

    /**
     * Return payment method extended title
     */
    public static function get_payment_method_extended_title($title, $payment_id)
    {
        try
        {   
            // - Only for yottapay payment method on checkout page
            if ($payment_id == WC_YottaPay_Common::GATEWAY_ID && is_checkout())
            {
                // -- Get plugin options
                $options = WC_YottaPay_Options::get_gateway_options();

                // -- init
                $formatted_discount_percent = 0;

                // -- Get gateway discount
                $gateway_discount = $options['gateway_discount'];

                // -- Get discount percent
                if ($gateway_discount > 0)
                {
                    // --- Gateway Discount
                    $formatted_discount_percent = floatval(number_format(round($gateway_discount, 2, PHP_ROUND_HALF_UP), 2, '.', ''));
                }
                else
                {
                    // --- Stripe discount
                    $meta_data_stripe_discount = WC_YottaPay_Options_Worker::get_yottapay_meta_data_option(WC_YottaPay_Options::YOTTAPAY_META_DATA_STRIPE_DISCOUNT);
                    if ($meta_data_stripe_discount != '' && $meta_data_stripe_discount != '-1' && $meta_data_stripe_discount != '0')
                    {
                        $discount_percent = floatval($meta_data_stripe_discount) / 100;
                        $formatted_discount_percent = floatval(number_format(round($discount_percent, 2, PHP_ROUND_HALF_UP), 2, '.', '')); 
                    }
                }

                // -- Set extended title
                if ($formatted_discount_percent > 0)
                {
                    $extended_title = $title  . ' ' . $formatted_discount_percent . '&#37; off';
                    return $extended_title;
                }
            }

            // - Return default title
            return $title;
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_Frontend_Common', 'get_payment_method_extended_title', $e->getMessage());

            //Return default title
            return $title;
        }
    }

    /**
     * Return payment method icon html
     */
    public static function get_payment_method_icon_html($icon, $gateway_id)
    {
        if($gateway_id == WC_YottaPay_Common::GATEWAY_ID)
        {
            // -- Get plugin options
            $options = WC_YottaPay_Options::get_gateway_options();

            // -- Check checkout_description_type value
            if ($options['checkout_description_type'] == 'option_card_payment')
            {
                if ($options['loyalty_bonus'] > 0)
                {
                    $icon_path = YOTTAPAY_PLUGIN_URL . 'assets/logo_card_payment_loyalty.png';
                }
                else
                {
                    $icon_path = YOTTAPAY_PLUGIN_URL . 'assets/logo_card_payment.png';
                }
            }
            else
            {
                if ($options['loyalty_bonus'] > 0)
                {
                    $icon_path = YOTTAPAY_PLUGIN_URL . 'assets/logo_mobile_banking_loyalty.png';
                }
                else
                {
                    $icon_path = YOTTAPAY_PLUGIN_URL . 'assets/logo_mobile_banking.png';
                }                
            }

            // -- Set icon html
            $icon = '
                <div style="margin: 5px 0; display: flex; align-items: center; max-width: min(300px, 75vw); padding: 4px 0; box-sizing: border-box;">
                    <img src="' . $icon_path . '" style="width: 100%; height: auto; max-height: none !important; object-fit: contain; position: inherit;">
                    <p hidden style="display: none">ver. ' . YOTTAPAY_PLUGIN_VERSION . '</p>
                </div>';
        }
   
        // - Return
        return $icon;
    }
}
