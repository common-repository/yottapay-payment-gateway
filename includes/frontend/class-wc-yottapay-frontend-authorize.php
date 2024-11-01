<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay Frontend Authorize class
 */
class WC_YottaPay_Frontend_Authorize
{
    /**
     * Modify plugin settings page in admin panel to display Login button
     */
    public static function modify_settings_checkout()
    {
        // - Check for plugin settings page
        $query_parameter_section = isset($_GET['section']) ? wc_clean(wp_unslash($_GET['section'])) : '';
        if ($query_parameter_section == '' || WC_YottaPay_Common::GATEWAY_ID !== $query_parameter_section)
        {
		    return;
	    }

        // - Get plugin options
        $options = WC_YottaPay_Options::get_gateway_options();

        // - Check for user_token
        $user_token = $options['user_token'];
        if ($user_token != '')
        {
            return;
        }

        // - Hide options fields
	    self::hide_settings_page_elements();

        // - Show Log In button
        self::show_login_button();
    }
    
    //---------------------------------------------------
    //  PRIVATE
    //---------------------------------------------------

    /**
     * Hide plugin settings page elements
     */
    private static function hide_settings_page_elements()
    {
       ?>
            <style>
                #mainform h2
                {
                display: none !important;
                }
                .yottapay-description
                {
                    display: none !important;
                }
                table.form-table
                {
                    display: none !important;
                }
                button.woocommerce-save-button
                {
                    display: none !important;
                }
            </style>
       <?php
    }

    /**
     * Show Login button
     */
    private static function show_login_button()
    {
       ?>
            <div id="boxYottaPayLogIn" style="text-align: center; margin-top: 100px; margin-left: calc(50% - 100px);">
                <a id="btnYottaPayLogIn" href="javascript:void(0)" style="display: block; width: 200px; max-width: 200px; text-decoration: none; color: white; font-weight: 600;" onclick="yottapayProcessAuthorize();">
                    <div style="padding-top: 10px; padding-bottom: 10px; margin-left: auto; margin-right: auto; width: 100%; border-radius: 12px; background-color: #00a0ff;">
                        <img src="<?php echo(YOTTAPAY_PLUGIN_URL . 'assets/icon_white.png')?>" style="width: 32px; height: 32px; vertical-align: middle;">
                        <span style="vertical-align: middle;">Log in with Yotta Pay</span>
                    </div>
                </a>
            </div>
       <?php
    }
}
